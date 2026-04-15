<?php

namespace Database\Factories;

use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Team>
 */
class TeamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->city().' FC',
            'short_name' => strtoupper(fake()->unique()->lexify('???')),
            'country' => fake()->randomElement(['Turkey', 'Spain', 'England', 'Germany', 'Italy']),
            'attack_rating' => fake()->numberBetween(70, 95),
            'defense_rating' => fake()->numberBetween(70, 95),
            'tactic_rating' => fake()->numberBetween(70, 95),
        ];
    }
}
