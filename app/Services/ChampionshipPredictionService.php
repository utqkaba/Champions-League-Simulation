<?php

namespace App\Services;

use App\Models\Team;
use Illuminate\Support\Collection;

class ChampionshipPredictionService
{
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
            fn (array $team) => (int) $team['points'] + ((6 - (int) $team['played']) * 3)
        )->max();
        $maxStrength = max(1, (int) $teams->map(
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
            $remainingMatches = max(0, 6 - $played);
            $maxPossiblePoints = $points + ($remainingMatches * 3);
            $pointGap = max(0, $leaderPoints - $points);
            $goalDifferenceGap = max(0, $leaderGoalDifference - $goalDifference);

            if (! $team || $maxPossiblePoints < $leaderPoints) {
                return [
                    'name' => (string) $standingRow['name'],
                    'weight' => 0,
                    'points' => $points,
                    'goal_difference' => $goalDifference,
                ];
            }

            $strengthTotal = $team->attack_rating + $team->defense_rating + $team->tactic_rating;

            $pointGapScore = max(0, 100 - ($pointGap * 18));
            $goalDifferenceScore = max(0, 100 - ($goalDifferenceGap * 4));
            $maxPossibleScore = ($maxPossiblePoints / max(1, $maxPossiblePointsInTable)) * 100;
            $strengthScore = ($strengthTotal / $maxStrength) * 100;

            $weight =
                ($pointGapScore * 0.40) +
                ($goalDifferenceScore * 0.20) +
                ($maxPossibleScore * 0.22) +
                ($strengthScore * 0.18);

            return [
                'name' => (string) $standingRow['name'],
                'weight' => $weight,
                'points' => $points,
                'goal_difference' => $goalDifference,
            ];
        });

        $totalWeight = $weights->sum('weight');

        if ($totalWeight <= 0) {
            return $weights->map(fn (array $team) => [
                'name' => $team['name'],
                'percentage' => 0,
                'points' => $team['points'],
                'goal_difference' => $team['goal_difference'],
            ])->values();
        }

        $rawPercentages = $weights->values()->map(function (array $team) use ($totalWeight): array {
            $exactPercentage = ($team['weight'] / $totalWeight) * 100;

            return [
                'name' => $team['name'],
                'floor' => (int) floor($exactPercentage),
                'remainder' => $exactPercentage - floor($exactPercentage),
                'points' => $team['points'],
                'goal_difference' => $team['goal_difference'],
            ];
        });

        $allocated = $rawPercentages->sum('floor');
        $remaining = 100 - $allocated;
        $bonusIndexes = $rawPercentages
            ->sortByDesc('remainder')
            ->take($remaining)
            ->keys()
            ->flip();

        return $rawPercentages
            ->map(function (array $team, int $index) use ($bonusIndexes): array {
                return [
                    'name' => $team['name'],
                    'percentage' => $team['floor'] + ($bonusIndexes->has($index) ? 1 : 0),
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
        return $standings->every(fn (array $team) => (int) $team['played'] >= 6);
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
                'percentage' => $champion && $team['name'] === $champion['name'] ? 100 : 0,
            ])
            ->values();
    }
}
