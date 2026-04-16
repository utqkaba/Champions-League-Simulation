<?php

namespace Tests\Feature;

use App\Models\Fixture;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class SimulationFlowTest extends TestCase
{
    use RefreshDatabase;

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

        $response->assertRedirect(route('simulator.simulation'));

        $this->assertDatabaseCount('fixtures', 12);
        $this->assertDatabaseMissing('fixtures', ['matchday' => 1, 'status' => 'scheduled']);
        $this->assertDatabaseHas('fixtures', ['matchday' => 2, 'status' => 'scheduled']);
    }

    public function test_play_all_weeks_endpoint_completes_remaining_matches(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->post(route('simulator.generate-fixtures'));

        $response = $this->post(route('simulator.play-all-weeks'));

        $response->assertRedirect(route('simulator.simulation', ['view' => 'all-weeks']));

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

        $response->assertRedirect(route('simulator.simulation'));

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
        $homeTeamName = $fixture->homeTeam->name;

        $simulationPage->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Simulator/Simulation')
            ->where('currentWeek', 1)
            ->where('currentWeekFixtures.0.home_goals', 2)
            ->where('currentWeekFixtures.0.away_goals', 0)
            ->where('standings.0.name', $homeTeamName)
            ->where('standings.0.points', 3)
            ->where('standings.0.goal_difference', 2));
    }
}
