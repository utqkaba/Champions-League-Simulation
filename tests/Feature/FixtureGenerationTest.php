<?php

namespace Tests\Feature;

use App\Models\Team;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FixtureGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_fixtures_endpoint_creates_fixture_list_and_redirects(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->post(route('simulator.generate-fixtures'));

        $response->assertRedirect(route('simulator.fixtures'));

        $this->assertDatabaseCount('fixtures', 12);
    }

    public function test_first_week_home_and_away_status_flips_in_second_week(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->post(route('simulator.generate-fixtures'));

        $weekOneFixtures = \App\Models\Fixture::query()->where('matchday', 1)->get();
        $weekTwoFixtures = \App\Models\Fixture::query()->where('matchday', 2)->get();

        foreach (Team::query()->get() as $team) {
            $playedAtHomeInWeekOne = $weekOneFixtures->contains('home_team_id', $team->id);
            $playedAtHomeInWeekTwo = $weekTwoFixtures->contains('home_team_id', $team->id);

            $this->assertNotSame($playedAtHomeInWeekOne, $playedAtHomeInWeekTwo);
        }
    }
}
