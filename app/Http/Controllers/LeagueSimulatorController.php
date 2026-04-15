<?php

namespace App\Http\Controllers;

use App\Models\Fixture;
use App\Models\Team;
use App\Services\ChampionshipPredictionService;
use App\Services\FixtureGeneratorService;
use App\Services\MatchSimulationService;
use App\Services\StandingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class LeagueSimulatorController extends Controller
{
    public function __construct(
        private readonly ChampionshipPredictionService $championshipPredictionService,
        private readonly FixtureGeneratorService $fixtureGeneratorService,
        private readonly StandingsService $standingsService,
        private readonly MatchSimulationService $matchSimulationService,
    ) {
    }

    public function teams(): Response
    {
        $teams = $this->teamsForDisplay();

        return Inertia::render('Simulator/TournamentTeams', [
            'teams' => $teams->map(fn (Team $team) => $team->only([
                'id',
                'name',
            ])),
        ]);
    }

    public function generateFixtures(): RedirectResponse
    {
        $teams = $this->teamsForDisplay();
        $fixtureMatrix = $this->fixtureGeneratorService->generate($teams);

        Fixture::query()->delete();

        foreach ($fixtureMatrix as $matchday => $matches) {
            foreach ($matches as $match) {
                Fixture::query()->create([
                    'matchday' => $matchday,
                    'home_team_id' => $match['home_team_id'],
                    'away_team_id' => $match['away_team_id'],
                    'home_goals' => null,
                    'away_goals' => null,
                    'status' => 'scheduled',
                ]);
            }
        }

        return to_route('simulator.fixtures');
    }

    public function fixtures(): Response|RedirectResponse
    {
        if (! Fixture::query()->exists()) {
            return to_route('simulator.teams');
        }

        return Inertia::render('Simulator/GeneratedFixtures', [
            'fixturesByMatchday' => $this->fixturesByMatchday(),
        ]);
    }

    public function simulation(): Response|RedirectResponse
    {
        if (! Fixture::query()->exists()) {
            return to_route('simulator.teams');
        }

        $standings = $this->standingsService->build(
            Team::query()->with(['homeFixtures', 'awayFixtures'])->get()
        )->values();
        $teams = $this->teamsForDisplay();

        $displayWeek = $this->displayWeek();

        return Inertia::render('Simulator/Simulation', [
            'currentWeek' => $displayWeek,
            'currentWeekFixtures' => $this->fixturesForWeek($displayWeek),
            'standings' => $standings,
            'championshipPredictions' => $this->championshipPredictionService->build($standings, $teams)->values(),
        ]);
    }

    public function playNextWeek(): RedirectResponse
    {
        $fixtures = Fixture::query()
            ->with(['homeTeam:id,attack_rating,defense_rating,tactic_rating', 'awayTeam:id,attack_rating,defense_rating,tactic_rating'])
            ->where('status', 'scheduled')
            ->where('matchday', $this->currentWeek())
            ->orderBy('matchday')
            ->orderBy('id')
            ->get();

        if ($fixtures->isEmpty()) {
            return to_route('simulator.simulation');
        }

        foreach ($fixtures as $fixture) {
            $this->matchSimulationService->simulate($fixture);
        }

        return to_route('simulator.simulation');
    }

    public function playAllWeeks(): RedirectResponse
    {
        $fixtures = Fixture::query()
            ->with(['homeTeam:id,attack_rating,defense_rating,tactic_rating', 'awayTeam:id,attack_rating,defense_rating,tactic_rating'])
            ->where('status', 'scheduled')
            ->orderBy('matchday')
            ->orderBy('id')
            ->get();

        if ($fixtures->isEmpty()) {
            return to_route('simulator.simulation');
        }

        foreach ($fixtures as $fixture) {
            $this->matchSimulationService->simulate($fixture);
        }

        return to_route('simulator.simulation');
    }

    public function resetData(): RedirectResponse
    {
        Fixture::query()->update([
            'home_goals' => null,
            'away_goals' => null,
            'status' => 'scheduled',
        ]);

        return to_route('simulator.simulation');
    }

    /**
     * @return \Illuminate\Support\Collection<int, \Illuminate\Support\Collection<int, array<string, mixed>>>
     */
    private function fixturesByMatchday()
    {
        return Fixture::query()
            ->with(['homeTeam:id,name,short_name', 'awayTeam:id,name,short_name'])
            ->orderBy('matchday')
            ->orderBy('id')
            ->get()
            ->map(fn (Fixture $fixture) => [
                'id' => $fixture->id,
                'matchday' => $fixture->matchday,
                'home_team' => $fixture->homeTeam?->only(['id', 'name', 'short_name']),
                'away_team' => $fixture->awayTeam?->only(['id', 'name', 'short_name']),
                'home_goals' => $fixture->home_goals,
                'away_goals' => $fixture->away_goals,
                'status' => $fixture->status,
                'is_completed' => $fixture->is_completed,
            ])
            ->groupBy('matchday')
            ->values();
    }

    /**
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function fixturesForWeek(int $week): Collection
    {
        return Fixture::query()
            ->with(['homeTeam:id,name,short_name', 'awayTeam:id,name,short_name'])
            ->where('matchday', $week)
            ->orderBy('id')
            ->get()
            ->map(fn (Fixture $fixture) => [
                'id' => $fixture->id,
                'matchday' => $fixture->matchday,
                'home_team' => $fixture->homeTeam?->only(['id', 'name', 'short_name']),
                'away_team' => $fixture->awayTeam?->only(['id', 'name', 'short_name']),
                'home_goals' => $fixture->home_goals,
                'away_goals' => $fixture->away_goals,
                'status' => $fixture->status,
                'is_completed' => $fixture->is_completed,
            ])
            ->values();
    }

    private function currentWeek(): int
    {
        return (int) (
            Fixture::query()
                ->where('status', 'scheduled')
                ->orderBy('matchday')
                ->value('matchday')
            ?? Fixture::query()->max('matchday')
            ?? 1
        );
    }

    private function displayWeek(): int
    {
        return (int) (
            Fixture::query()
                ->where('status', 'completed')
                ->max('matchday')
            ?? $this->currentWeek()
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Team>
     */
    private function teamsForDisplay(): Collection
    {
        $order = ['Liverpool', 'Manchester City', 'Newcastle United', 'Arsenal'];

        return Team::query()
            ->get()
            ->sortBy(fn (Team $team) => array_search($team->name, $order, true))
            ->values();
    }
}
