<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use App\Models\Fixture;
use App\Models\Team;
use App\Services\ChampionshipPredictionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->get('/');

        $response
            ->assertStatus(200)
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Simulator/TournamentTeams')
                ->has('teams', 4));
    }

    public function test_generate_fixtures_endpoint_creates_fixture_list_and_redirects(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->post(route('simulator.generate-fixtures'));

        $response
            ->assertRedirect(route('simulator.fixtures'));

        $this->assertDatabaseCount('fixtures', 12);
    }

    public function test_simulation_page_can_be_opened_without_playing_matches(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->post(route('simulator.generate-fixtures'));

        $response = $this->get(route('simulator.simulation'));

        $response
            ->assertStatus(200)
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Simulator/Simulation')
                ->where('currentWeek', 1)
                ->has('currentWeekFixtures', 2)
                ->has('fixturesByMatchday', 6)
                ->where('showAllWeeksResults', false)
                ->has('standings', 4)
                ->has('championshipPredictions', 4)
                ->where('championshipPredictions.0.percentage', fn ($value) => is_int($value)));
    }

    public function test_play_next_week_endpoint_completes_only_first_scheduled_week(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->post(route('simulator.generate-fixtures'));

        $response = $this->post(route('simulator.play-next-week'));

        $response
            ->assertRedirect(route('simulator.simulation'));

        $this->assertDatabaseCount('fixtures', 12);
        $this->assertDatabaseMissing('fixtures', ['matchday' => 1, 'status' => 'scheduled']);
        $this->assertDatabaseHas('fixtures', ['matchday' => 2, 'status' => 'scheduled']);
    }

    public function test_play_all_weeks_endpoint_completes_remaining_matches(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->post(route('simulator.generate-fixtures'));

        $response = $this->post(route('simulator.play-all-weeks'));

        $response
            ->assertRedirect(route('simulator.simulation', ['view' => 'all-weeks']));

        $this->assertDatabaseMissing('fixtures', ['status' => 'scheduled']);

        $simulationPage = $this->get(route('simulator.simulation', ['view' => 'all-weeks']));

        $simulationPage->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Simulator/Simulation')
            ->where('showAllWeeksResults', true)
            ->has('fixturesByMatchday', 6)
            ->where('fixturesByMatchday.0.0.is_completed', true)
            ->where('fixturesByMatchday.5.1.is_completed', true));
    }

    public function test_reset_data_keeps_user_on_simulation_page_and_clears_scores(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->post(route('simulator.generate-fixtures'));
        $this->post(route('simulator.play-next-week'));

        $response = $this->post(route('simulator.reset-data'));

        $response
            ->assertRedirect(route('simulator.simulation'));

        $this->assertDatabaseCount('fixtures', 12);
        $this->assertDatabaseMissing('fixtures', ['status' => 'completed']);
        $this->assertDatabaseMissing('fixtures', ['home_goals' => 1]);
    }

    public function test_fixture_results_can_be_edited_and_standings_are_recalculated(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->post(route('simulator.generate-fixtures'));

        $fixture = Fixture::query()
            ->where('matchday', 1)
            ->orderBy('id')
            ->firstOrFail();

        $response = $this->patch(route('simulator.update-fixture-result', $fixture), [
            'home_goals' => 2,
            'away_goals' => 0,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('fixtures', [
            'id' => $fixture->id,
            'home_goals' => 2,
            'away_goals' => 0,
            'status' => 'completed',
        ]);

        $simulationPage = $this->get(route('simulator.simulation'));

        $simulationPage->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Simulator/Simulation')
            ->where('currentWeek', 1)
            ->where('currentWeekFixtures.0.home_goals', 2)
            ->where('currentWeekFixtures.0.away_goals', 0)
            ->where('standings.0.name', 'Arsenal')
            ->where('standings.0.points', 3)
            ->where('standings.0.goal_difference', 2));
    }

    public function test_championship_predictions_respect_goal_difference_when_points_are_equal(): void
    {
        $service = app(ChampionshipPredictionService::class);

        $standings = collect([
            [
                'name' => 'Liverpool',
                'points' => 10,
                'played' => 4,
                'goal_difference' => 7,
            ],
            [
                'name' => 'Arsenal',
                'points' => 10,
                'played' => 4,
                'goal_difference' => 3,
            ],
        ]);

        $teams = collect([
            new Team([
                'name' => 'Liverpool',
                'attack_rating' => 84,
                'defense_rating' => 82,
                'tactic_rating' => 82,
            ]),
            new Team([
                'name' => 'Arsenal',
                'attack_rating' => 84,
                'defense_rating' => 86,
                'tactic_rating' => 86,
            ]),
        ]);

        $predictions = $service->build($standings, $teams);

        $this->assertSame('Liverpool', $predictions->first()['name']);
        $this->assertGreaterThanOrEqual(
            $predictions->last()['percentage'],
            $predictions->first()['percentage']
        );
    }

    public function test_championship_predictions_become_certain_when_league_is_finished(): void
    {
        $service = app(ChampionshipPredictionService::class);

        $standings = collect([
            [
                'name' => 'Manchester City',
                'points' => 9,
                'played' => 6,
                'goal_difference' => 3,
                'goals_for' => 8,
            ],
            [
                'name' => 'Liverpool',
                'points' => 9,
                'played' => 6,
                'goal_difference' => -1,
                'goals_for' => 5,
            ],
            [
                'name' => 'Arsenal',
                'points' => 6,
                'played' => 6,
                'goal_difference' => -2,
                'goals_for' => 4,
            ],
            [
                'name' => 'Newcastle United',
                'points' => 6,
                'played' => 6,
                'goal_difference' => 0,
                'goals_for' => 6,
            ],
        ]);

        $teams = collect([
            new Team([
                'name' => 'Manchester City',
                'attack_rating' => 88,
                'defense_rating' => 85,
                'tactic_rating' => 87,
            ]),
            new Team([
                'name' => 'Liverpool',
                'attack_rating' => 90,
                'defense_rating' => 84,
                'tactic_rating' => 86,
            ]),
            new Team([
                'name' => 'Arsenal',
                'attack_rating' => 84,
                'defense_rating' => 82,
                'tactic_rating' => 84,
            ]),
            new Team([
                'name' => 'Newcastle United',
                'attack_rating' => 82,
                'defense_rating' => 80,
                'tactic_rating' => 81,
            ]),
        ]);

        $predictions = $service->build($standings, $teams);

        $this->assertSame('Manchester City', $predictions->first()['name']);
        $this->assertSame(100, $predictions->first()['percentage']);
        $this->assertSame(0, $predictions->get(1)['percentage']);
    }
}
