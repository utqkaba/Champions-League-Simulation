<?php

namespace App\Services;

use Illuminate\Support\Collection;

class FixtureGeneratorService
{
    private const EXPECTED_TEAM_COUNT = 4;
    private const FIRST_LEG_MATCHDAYS = 3;
    private const RETURN_LEG_OFFSET = 3;
    private const FIRST_TEAM_INDEX = 0;
    private const SECOND_TEAM_INDEX = 1;
    private const THIRD_TEAM_INDEX = 2;
    private const FOURTH_TEAM_INDEX = 3;

    /**
     * Build a six-matchday double round-robin fixture list for four teams.
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\Team>  $teams
     * @return array<int, array<int, array<string, int>>>
     */
    public function generate(Collection $teams): array
    {
        $teamList = $teams->shuffle()->values();

        if ($teamList->count() !== self::EXPECTED_TEAM_COUNT) {
            return [];
        }

        $firstLegFixtures = $this->buildFirstLegFixtures($teamList);

        return $firstLegFixtures + $this->buildReturnLegFixtures($firstLegFixtures);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\Team>  $teams
     * @return array<int, array<int, array<string, int>>>
     */
    private function buildFirstLegFixtures(Collection $teams): array
    {
        $rotation = $teams->values()->all();

        return [
            1 => [
                $this->fixture($rotation[self::FIRST_TEAM_INDEX], $rotation[self::FOURTH_TEAM_INDEX]),
                $this->fixture($rotation[self::SECOND_TEAM_INDEX], $rotation[self::THIRD_TEAM_INDEX]),
            ],
            2 => [
                $this->fixture($rotation[self::THIRD_TEAM_INDEX], $rotation[self::FIRST_TEAM_INDEX]),
                $this->fixture($rotation[self::FOURTH_TEAM_INDEX], $rotation[self::SECOND_TEAM_INDEX]),
            ],
            3 => [
                $this->fixture($rotation[self::FIRST_TEAM_INDEX], $rotation[self::SECOND_TEAM_INDEX]),
                $this->fixture($rotation[self::THIRD_TEAM_INDEX], $rotation[self::FOURTH_TEAM_INDEX]),
            ],
        ];
    }

    /**
     * @param  array<int, array<int, array<string, int>>>  $firstLegFixtures
     * @return array<int, array<int, array<string, int>>>
     */
    private function buildReturnLegFixtures(array $firstLegFixtures): array
    {
        $returnLegFixtures = [];

        foreach ($firstLegFixtures as $matchday => $fixtures) {
            $returnLegFixtures[$matchday + self::RETURN_LEG_OFFSET] = array_map(
                fn (array $fixture) => [
                    'home_team_id' => $fixture['away_team_id'],
                    'away_team_id' => $fixture['home_team_id'],
                ],
                $fixtures
            );
        }

        return $returnLegFixtures;
    }

    /**
     * @param  \App\Models\Team  $homeTeam
     * @param  \App\Models\Team  $awayTeam
     * @return array<string, int>
     */
    private function fixture(object $homeTeam, object $awayTeam): array
    {
        return [
            'home_team_id' => $homeTeam->id,
            'away_team_id' => $awayTeam->id,
        ];
    }
}
