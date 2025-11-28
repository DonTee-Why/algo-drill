<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProblemTest>
 */
class ProblemTestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'problem_id' => \App\Models\Problem::factory(),
            'input' => [
                'nums' => [fake()->numberBetween(1, 100), fake()->numberBetween(1, 100)],
                'target' => fake()->numberBetween(1, 200),
            ],
            'expected' => fake()->numberBetween(0, 100),
            'is_edge' => fake()->boolean(20),
            'weight' => fake()->numberBetween(1, 3),
        ];
    }
}
