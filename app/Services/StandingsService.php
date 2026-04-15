<?php

namespace App\Services;

use App\Models\Team;
use Illuminate\Support\Collection;

class StandingsService
{
    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\Team>  $teams
     * @return \Illuminate\Support\Collection<int, array<string, int|string>>
     */
    public function build(Collection $teams): Collection
    {
        $rows = $teams->map(function (Team $team): array {
            return [
                'id' => $team->id,
                'name' => $team->name,
                'short_name' => $team->short_name,
                'played' => 0,
                'won' => 0,
                'drawn' => 0,
                'lost' => 0,
                'goals_for' => 0,
                'goals_against' => 0,
                'goal_difference' => 0,
                'points' => 0,
            ];
        })->keyBy('id')->all();

        foreach ($teams as $team) {
            foreach ($team->homeFixtures->merge($team->awayFixtures) as $fixture) {
                if (! $fixture->is_completed) {
                    continue;
                }

                $isHome = $fixture->home_team_id === $team->id;
                $goalsFor = $isHome ? $fixture->home_goals : $fixture->away_goals;
                $goalsAgainst = $isHome ? $fixture->away_goals : $fixture->home_goals;

                $rows[$team->id]['played']++;
                $rows[$team->id]['goals_for'] += $goalsFor;
                $rows[$team->id]['goals_against'] += $goalsAgainst;

                if ($goalsFor > $goalsAgainst) {
                    $rows[$team->id]['won']++;
                    $rows[$team->id]['points'] += 3;
                } elseif ($goalsFor === $goalsAgainst) {
                    $rows[$team->id]['drawn']++;
                    $rows[$team->id]['points']++;
                } else {
                    $rows[$team->id]['lost']++;
                }

                $rows[$team->id]['goal_difference'] =
                    $rows[$team->id]['goals_for'] - $rows[$team->id]['goals_against'];
            }
        }

        return collect($rows)
            ->sortByDesc(fn (array $row) => [
                $row['points'],
                $row['goal_difference'],
                $row['goals_for'],
            ])
            ->values();
    }
}
