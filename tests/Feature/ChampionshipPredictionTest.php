<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Services\ChampionshipPredictionService;
use Tests\TestCase;

class ChampionshipPredictionTest extends TestCase
{
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
