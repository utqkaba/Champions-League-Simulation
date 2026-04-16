<?php

namespace App\Services;

use Illuminate\Support\Collection;

class FixtureGeneratorService
{
    private const EXPECTED_TEAM_COUNT = 4;
    private const FIRST_MATCHDAY = 1;
    private const SECOND_MATCHDAY = 2;
    private const THIRD_MATCHDAY = 3;
    private const FOURTH_MATCHDAY = 4;
    private const FIFTH_MATCHDAY = 5;
    private const SIXTH_MATCHDAY = 6;

    /**
     * Build a six-matchday double round-robin fixture list for four teams.
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\Team>  $teams
     * @return array<int, array<int, array<string, int>>>
     */
    public function generate(Collection $teams): array
    {
        $teamMap = $teams->keyBy('name');

        if ($teamMap->count() !== self::EXPECTED_TEAM_COUNT) {
            return [];
        }

        $arsenal = $teamMap->get('Arsenal');
        $manchesterCity = $teamMap->get('Manchester City');
        $newcastleUnited = $teamMap->get('Newcastle United');
        $liverpool = $teamMap->get('Liverpool');

        if (! $arsenal || ! $manchesterCity || ! $newcastleUnited || ! $liverpool) {
            return [];
        }

        return [
            self::FIRST_MATCHDAY => [
                ['home_team_id' => $arsenal->id, 'away_team_id' => $liverpool->id],
                ['home_team_id' => $manchesterCity->id, 'away_team_id' => $newcastleUnited->id],
            ],
            self::SECOND_MATCHDAY => [
                ['home_team_id' => $manchesterCity->id, 'away_team_id' => $arsenal->id],
                ['home_team_id' => $newcastleUnited->id, 'away_team_id' => $liverpool->id],
            ],
            self::THIRD_MATCHDAY => [
                ['home_team_id' => $arsenal->id, 'away_team_id' => $newcastleUnited->id],
                ['home_team_id' => $liverpool->id, 'away_team_id' => $manchesterCity->id],
            ],
            self::FOURTH_MATCHDAY => [
                ['home_team_id' => $liverpool->id, 'away_team_id' => $arsenal->id],
                ['home_team_id' => $newcastleUnited->id, 'away_team_id' => $manchesterCity->id],
            ],
            self::FIFTH_MATCHDAY => [
                ['home_team_id' => $arsenal->id, 'away_team_id' => $manchesterCity->id],
                ['home_team_id' => $liverpool->id, 'away_team_id' => $newcastleUnited->id],
            ],
            self::SIXTH_MATCHDAY => [
                ['home_team_id' => $newcastleUnited->id, 'away_team_id' => $arsenal->id],
                ['home_team_id' => $manchesterCity->id, 'away_team_id' => $liverpool->id],
            ],
        ];
    }
}
