<?php

namespace App\Services;

use App\Models\Fixture;

class MatchSimulationService
{
    public function simulate(Fixture $fixture): void
    {
        $homeAttack = $fixture->homeTeam->attack_rating + 5;
        $homeDefense = $fixture->homeTeam->defense_rating + 5;
        $homeTactic = $fixture->homeTeam->tactic_rating + 5;

        $awayAttack = $fixture->awayTeam->attack_rating;
        $awayDefense = $fixture->awayTeam->defense_rating;
        $awayTactic = $fixture->awayTeam->tactic_rating;

        $homePower =
            ($homeAttack * 0.40) +
            ($homeTactic * 0.25) +
            (($homeTactic - $awayTactic) * 0.10);

        $awayPower =
            ($awayAttack * 0.35) +
            ($awayTactic * 0.25) +
            (($awayTactic - $homeTactic) * 0.10);

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
        $attackOutput = 0.5 + ($power / 100) * 1.6;
        $defensivePressure = (($opponentDefense - 50) / 50) * 0.55;

        return min(3.2, max(0.15, $attackOutput - $defensivePressure));
    }

    private function goalCount(float $expectedGoals): int
    {
        $threshold = exp(-$expectedGoals);
        $product = 1.0;
        $goals = 0;

        do {
            $goals++;
            $product *= mt_rand(1, 1000000) / 1000000;
        } while ($product > $threshold);

        return $goals - 1;
    }
}
