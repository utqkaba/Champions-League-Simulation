<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $teams = [
            [
                'name' => 'Liverpool',
                'short_name' => 'LIV',
                'country' => 'England',
                'attack_rating' => 86,
                'defense_rating' => 84,
                'tactic_rating' => 84,
            ],
            [
                'name' => 'Manchester City',
                'short_name' => 'MCI',
                'country' => 'England',
                'attack_rating' => 91,
                'defense_rating' => 87,
                'tactic_rating' => 95,
            ],
            [
                'name' => 'Newcastle United',
                'short_name' => 'NEW',
                'country' => 'England',
                'attack_rating' => 72,
                'defense_rating' => 74,
                'tactic_rating' => 70,
            ],
            [
                'name' => 'Arsenal',
                'short_name' => 'ARS',
                'country' => 'England',
                'attack_rating' => 82,
                'defense_rating' => 92,
                'tactic_rating' => 90,
            ],
        ];

        foreach ($teams as $team) {
            Team::query()->updateOrCreate(
                ['name' => $team['name']],
                $team,
            );
        }
    }
}
