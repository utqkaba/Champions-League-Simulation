<?php

namespace App\Services;

use Illuminate\Support\Collection;

class FixtureGeneratorService
{
    /**
     * Build a six-matchday double round-robin fixture list for four teams.
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\Team>  $teams
     * @return array<int, array<int, array<string, int>>>
     */
    public function generate(Collection $teams): array
    {
        $teamMap = $teams->keyBy('name');

        if ($teamMap->count() !== 4) {
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
            1 => [
                ['home_team_id' => $arsenal->id, 'away_team_id' => $liverpool->id],
                ['home_team_id' => $manchesterCity->id, 'away_team_id' => $newcastleUnited->id],
            ],
            2 => [
                ['home_team_id' => $manchesterCity->id, 'away_team_id' => $arsenal->id],
                ['home_team_id' => $newcastleUnited->id, 'away_team_id' => $liverpool->id],
            ],
            3 => [
                ['home_team_id' => $arsenal->id, 'away_team_id' => $newcastleUnited->id],
                ['home_team_id' => $liverpool->id, 'away_team_id' => $manchesterCity->id],
            ],
            4 => [
                ['home_team_id' => $liverpool->id, 'away_team_id' => $arsenal->id],
                ['home_team_id' => $newcastleUnited->id, 'away_team_id' => $manchesterCity->id],
            ],
            5 => [
                ['home_team_id' => $arsenal->id, 'away_team_id' => $manchesterCity->id],
                ['home_team_id' => $liverpool->id, 'away_team_id' => $newcastleUnited->id],
            ],
            6 => [
                ['home_team_id' => $newcastleUnited->id, 'away_team_id' => $arsenal->id],
                ['home_team_id' => $manchesterCity->id, 'away_team_id' => $liverpool->id],
            ],
        ];
    }
}
