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
                'attack_rating' => 84,
                'defense_rating' => 80,
                'tactic_rating' => 80,
            ],
            [
                'name' => 'Manchester City',
                'short_name' => 'MCI',
                'country' => 'England',
                'attack_rating' => 92,
                'defense_rating' => 88,
                'tactic_rating' => 88,
            ],
            [
                'name' => 'Newcastle United',
                'short_name' => 'NEW',
                'country' => 'England',
                'attack_rating' => 60,
                'defense_rating' => 52,
                'tactic_rating' => 56,
            ],
            [
                'name' => 'Arsenal',
                'short_name' => 'ARS',
                'country' => 'England',
                'attack_rating' => 86,
                'defense_rating' => 90,
                'tactic_rating' => 86,
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
