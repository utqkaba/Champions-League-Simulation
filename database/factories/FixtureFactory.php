<?php

namespace Database\Factories;

use App\Models\Fixture;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Fixture>
 */
class FixtureFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'matchday' => fake()->numberBetween(1, 6),
            'home_goals' => fake()->optional()->numberBetween(0, 4),
            'away_goals' => fake()->optional()->numberBetween(0, 4),
            'status' => fake()->randomElement(['scheduled', 'completed']),
        ];
    }
}
