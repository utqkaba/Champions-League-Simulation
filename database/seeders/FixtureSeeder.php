<?php

namespace Database\Seeders;

use App\Models\Fixture;
use App\Models\Team;
use App\Services\FixtureGeneratorService;
use Illuminate\Database\Seeder;

class FixtureSeeder extends Seeder
{
    public function __construct(
        private readonly FixtureGeneratorService $fixtureGenerator,
    ) {
    }

    public function run(): void
    {
        $teams = Team::query()->orderBy('id')->get();
        $fixtureMatrix = $this->fixtureGenerator->generate($teams);

        foreach ($fixtureMatrix as $matchday => $matches) {
            foreach ($matches as $index => $match) {
                $sampleScore = match ([$matchday, $index]) {
                    [1, 0] => ['home_goals' => 2, 'away_goals' => 1, 'status' => 'completed'],
                    [1, 1] => ['home_goals' => 1, 'away_goals' => 1, 'status' => 'completed'],
                    [2, 0] => ['home_goals' => 0, 'away_goals' => 2, 'status' => 'completed'],
                    [2, 1] => ['home_goals' => 2, 'away_goals' => 2, 'status' => 'completed'],
                    default => ['home_goals' => null, 'away_goals' => null, 'status' => 'scheduled'],
                };

                Fixture::query()->updateOrCreate(
                    [
                        'matchday' => $matchday,
                        'home_team_id' => $match['home_team_id'],
                        'away_team_id' => $match['away_team_id'],
                    ],
                    array_merge($match, $sampleScore),
                );
            }
        }
    }
}
