<?php

namespace App\Services;

use App\Models\Team;
use Illuminate\Support\Collection;

class ChampionshipPredictionService
{
    private const LEAGUE_RULES = [
        'total_matchdays' => 6,
        'points_per_win' => 3,
    ];

    private const SCORE_RULES = [
        'max_score' => 100,
        'min_score' => 0,
        'percentage_scale' => 100,
        'minimum_divisor' => 1,
    ];

    private const GAP_PENALTIES = [
        'point_gap' => 20,
        'goal_difference_gap' => 4,
    ];

    private const PREDICTION_WEIGHTS = [
        'point_gap' => 0.40,
        'goal_difference' => 0.20,
        'max_possible_points' => 0.22,
        'strength' => 0.18,
    ];

    private const OUTCOME_DISTRIBUTION = [
        'certain_champion' => 100,
        'eliminated' => 0,
        'bonus_increment' => 1,
    ];

    /**
     * @param  \Illuminate\Support\Collection<int, array<string, int|string>>  $standings
     * @param  \Illuminate\Support\Collection<int, \App\Models\Team>  $teams
     * @return \Illuminate\Support\Collection<int, array{name: string, percentage: int}>
     */
    public function build(Collection $standings, Collection $teams): Collection
    {
        if ($this->leagueIsFinished($standings)) {
            return $this->finalizedPredictions($standings);
        }

        $teamsByName = $teams->keyBy('name');
        $leaderPoints = (int) $standings->max('points');
        $leaderGoalDifference = (int) $standings
            ->where('points', $leaderPoints)
            ->max('goal_difference');
        $maxPossiblePointsInTable = (int) $standings->map(
            fn (array $team) => (int) $team['points'] + ((self::LEAGUE_RULES['total_matchdays'] - (int) $team['played']) * self::LEAGUE_RULES['points_per_win'])
        )->max();
        $maxStrength = max(self::SCORE_RULES['minimum_divisor'], (int) $teams->map(
            fn (Team $team) => $team->attack_rating + $team->defense_rating + $team->tactic_rating
        )->max());

        $weights = $standings->map(function (array $team): array {
            return [
                'name' => (string) $team['name'],
                'weight' => 0,
            ];
        });

        $weights = $standings->map(function (array $standingRow) use ($teamsByName, $leaderPoints, $leaderGoalDifference, $maxPossiblePointsInTable, $maxStrength): array {
            $team = $teamsByName->get($standingRow['name']);
            $played = (int) $standingRow['played'];
            $points = (int) $standingRow['points'];
            $goalDifference = (int) $standingRow['goal_difference'];
            $remainingMatches = max(self::SCORE_RULES['min_score'], self::LEAGUE_RULES['total_matchdays'] - $played);
            $maxPossiblePoints = $points + ($remainingMatches * self::LEAGUE_RULES['points_per_win']);
            $pointGap = max(self::SCORE_RULES['min_score'], $leaderPoints - $points);
            $goalDifferenceGap = max(self::SCORE_RULES['min_score'], $leaderGoalDifference - $goalDifference);

            if (! $team || $maxPossiblePoints < $leaderPoints) {
                return [
                    'name' => (string) $standingRow['name'],
                    'weight' => self::OUTCOME_DISTRIBUTION['eliminated'],
                    'points' => $points,
                    'goal_difference' => $goalDifference,
                ];
            }

            $strengthTotal = $team->attack_rating + $team->defense_rating + $team->tactic_rating;

            $pointGapScore = max(
                self::SCORE_RULES['min_score'],
                self::SCORE_RULES['max_score'] - ($pointGap * self::GAP_PENALTIES['point_gap'])
            );
            $goalDifferenceScore = max(
                self::SCORE_RULES['min_score'],
                self::SCORE_RULES['max_score'] - ($goalDifferenceGap * self::GAP_PENALTIES['goal_difference_gap'])
            );
            $maxPossibleScore = ($maxPossiblePoints / max(self::SCORE_RULES['minimum_divisor'], $maxPossiblePointsInTable)) * self::SCORE_RULES['percentage_scale'];
            $strengthScore = ($strengthTotal / $maxStrength) * self::SCORE_RULES['percentage_scale'];

            $weight =
                ($pointGapScore * self::PREDICTION_WEIGHTS['point_gap']) +
                ($goalDifferenceScore * self::PREDICTION_WEIGHTS['goal_difference']) +
                ($maxPossibleScore * self::PREDICTION_WEIGHTS['max_possible_points']) +
                ($strengthScore * self::PREDICTION_WEIGHTS['strength']);

            return [
                'name' => (string) $standingRow['name'],
                'weight' => $weight,
                'points' => $points,
                'goal_difference' => $goalDifference,
            ];
        });

        $totalWeight = $weights->sum('weight');

        if ($totalWeight <= self::OUTCOME_DISTRIBUTION['eliminated']) {
            return $weights->map(fn (array $team) => [
                'name' => $team['name'],
                'percentage' => self::OUTCOME_DISTRIBUTION['eliminated'],
                'points' => $team['points'],
                'goal_difference' => $team['goal_difference'],
            ])->values();
        }

        $rawPercentages = $weights->values()->map(function (array $team) use ($totalWeight): array {
            $exactPercentage = ($team['weight'] / $totalWeight) * self::SCORE_RULES['percentage_scale'];

            return [
                'name' => $team['name'],
                'floor' => (int) floor($exactPercentage),
                'remainder' => $exactPercentage - floor($exactPercentage),
                'points' => $team['points'],
                'goal_difference' => $team['goal_difference'],
            ];
        });

        $allocated = $rawPercentages->sum('floor');
        $remaining = self::SCORE_RULES['percentage_scale'] - $allocated;
        $bonusIndexes = $rawPercentages
            ->sortByDesc('remainder')
            ->take($remaining)
            ->keys()
            ->flip();

        return $rawPercentages
            ->map(function (array $team, int $index) use ($bonusIndexes): array {
                return [
                    'name' => $team['name'],
                    'percentage' => $team['floor'] + (
                        $bonusIndexes->has($index)
                            ? self::OUTCOME_DISTRIBUTION['bonus_increment']
                            : self::OUTCOME_DISTRIBUTION['eliminated']
                    ),
                    'points' => $team['points'],
                    'goal_difference' => $team['goal_difference'],
                ];
            })
            ->sortByDesc(fn (array $team) => [
                $team['percentage'],
                $team['points'],
                $team['goal_difference'],
            ])
            ->map(fn (array $team) => [
                'name' => $team['name'],
                'percentage' => $team['percentage'],
            ])
            ->values();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, array<string, int|string>>  $standings
     */
    private function leagueIsFinished(Collection $standings): bool
    {
        return $standings->every(
            fn (array $team) => (int) $team['played'] >= self::LEAGUE_RULES['total_matchdays']
        );
    }

    /**
     * @param  \Illuminate\Support\Collection<int, array<string, int|string>>  $standings
     * @return \Illuminate\Support\Collection<int, array{name: string, percentage: int}>
     */
    private function finalizedPredictions(Collection $standings): Collection
    {
        $sortedStandings = $standings
            ->sortByDesc(fn (array $team) => [
                $team['points'],
                $team['goal_difference'],
                $team['goals_for'] ?? 0,
            ])
            ->values();

        $champion = $sortedStandings->first();

        return $sortedStandings
            ->map(fn (array $team) => [
                'name' => (string) $team['name'],
                'percentage' => $champion && $team['name'] === $champion['name']
                    ? self::OUTCOME_DISTRIBUTION['certain_champion']
                    : self::OUTCOME_DISTRIBUTION['eliminated'],
            ])
            ->values();
    }
}
