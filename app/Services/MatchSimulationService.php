<?php

namespace App\Services;

use App\Models\Fixture;

class MatchSimulationService
{
    private const HOME_ADVANTAGE = [
        'attack_bonus' => 5,
        'defense_bonus' => 5,
        'tactic_bonus' => 5,
    ];

    private const POWER_WEIGHTS = [
        'home_attack' => 0.40,
        'home_tactic' => 0.25,
        'away_attack' => 0.35,
        'away_tactic' => 0.25,
        'tactic_difference' => 0.10,
    ];

    private const EXPECTED_GOALS_RULES = [
        'base' => 0.5,
        'rating_scale' => 100,
        'attack_output_multiplier' => 1.6,
        'defense_baseline' => 50,
        'defensive_pressure_multiplier' => 0.55,
        'max' => 3.2,
        'min' => 0.15,
    ];

    private const GOAL_GENERATION_RULES = [
        'initial_product' => 1.0,
        'random_min' => 1,
        'random_max' => 1000000,
        'goal_offset' => 1,
    ];

    public function simulate(Fixture $fixture): void
    {
        $homeAttack = $fixture->homeTeam->attack_rating + self::HOME_ADVANTAGE['attack_bonus'];
        $homeDefense = $fixture->homeTeam->defense_rating + self::HOME_ADVANTAGE['defense_bonus'];
        $homeTactic = $fixture->homeTeam->tactic_rating + self::HOME_ADVANTAGE['tactic_bonus'];

        $awayAttack = $fixture->awayTeam->attack_rating;
        $awayDefense = $fixture->awayTeam->defense_rating;
        $awayTactic = $fixture->awayTeam->tactic_rating;

        $homePower =
            ($homeAttack * self::POWER_WEIGHTS['home_attack']) +
            ($homeTactic * self::POWER_WEIGHTS['home_tactic']) +
            (($homeTactic - $awayTactic) * self::POWER_WEIGHTS['tactic_difference']);

        $awayPower =
            ($awayAttack * self::POWER_WEIGHTS['away_attack']) +
            ($awayTactic * self::POWER_WEIGHTS['away_tactic']) +
            (($awayTactic - $homeTactic) * self::POWER_WEIGHTS['tactic_difference']);

        $homeExpectedGoals = $this->expectedGoals($homePower, $awayDefense);
        $awayExpectedGoals = $this->expectedGoals($awayPower, $homeDefense);

        $fixture->update([
            'home_goals' => $this->goalCount($homeExpectedGoals),
            'away_goals' => $this->goalCount($awayExpectedGoals),
            'status' => 'completed',
        ]);
    }

    private function expectedGoals(float $power, int $opponentDefense): float
    {
        $attackOutput = self::EXPECTED_GOALS_RULES['base']
            + ($power / self::EXPECTED_GOALS_RULES['rating_scale']) * self::EXPECTED_GOALS_RULES['attack_output_multiplier'];
        $defensivePressure = (($opponentDefense - self::EXPECTED_GOALS_RULES['defense_baseline']) / self::EXPECTED_GOALS_RULES['defense_baseline'])
            * self::EXPECTED_GOALS_RULES['defensive_pressure_multiplier'];

        return min(
            self::EXPECTED_GOALS_RULES['max'],
            max(self::EXPECTED_GOALS_RULES['min'], $attackOutput - $defensivePressure)
        );
    }

    private function goalCount(float $expectedGoals): int
    {
        $threshold = exp(-$expectedGoals);
        $product = self::GOAL_GENERATION_RULES['initial_product'];
        $goals = 0;

        do {
            $goals++;
            $product *= mt_rand(
                self::GOAL_GENERATION_RULES['random_min'],
                self::GOAL_GENERATION_RULES['random_max']
            ) / self::GOAL_GENERATION_RULES['random_max'];
        } while ($product > $threshold);

        return $goals - self::GOAL_GENERATION_RULES['goal_offset'];
    }
}
