<?php

namespace App\Services;

use App\Models\Team;
use Illuminate\Support\Collection;

class StandingsService
{
    private const INITIAL_STAT = 0;
    private const POINTS_PER_WIN = 3;
    private const POINTS_PER_DRAW = 1;

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
                'played' => self::INITIAL_STAT,
                'won' => self::INITIAL_STAT,
                'drawn' => self::INITIAL_STAT,
                'lost' => self::INITIAL_STAT,
                'goals_for' => self::INITIAL_STAT,
                'goals_against' => self::INITIAL_STAT,
                'goal_difference' => self::INITIAL_STAT,
                'points' => self::INITIAL_STAT,
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
                    $rows[$team->id]['points'] += self::POINTS_PER_WIN;
                } elseif ($goalsFor === $goalsAgainst) {
                    $rows[$team->id]['drawn']++;
                    $rows[$team->id]['points'] += self::POINTS_PER_DRAW;
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
