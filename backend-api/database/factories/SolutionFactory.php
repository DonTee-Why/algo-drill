<?php

namespace Database\Factories;

use App\Enums\Lang;
use App\Models\Problem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Solution>
 */
class SolutionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'problem_id' => Problem::factory(),
            'lang' => fake()->randomElement(Lang::cases()),
            'pseudocode' => fake()->paragraphs(3, true),
            'reference_code' => fake()->text(500),
            'explanation' => fake()->paragraphs(2, true),
            'complexity' => [
                'time' => fake()->randomElement(['O(1)', 'O(n)', 'O(n log n)', 'O(n^2)']),
                'space' => fake()->randomElement(['O(1)', 'O(n)', 'O(log n)']),
            ],
            'alternatives' => fake()->optional()->randomElements([
                ['name' => 'Brute Force', 'time' => 'O(n^2)', 'space' => 'O(1)'],
                ['name' => 'Optimized', 'time' => 'O(n)', 'space' => 'O(n)'],
                ['name' => 'Space Optimized', 'time' => 'O(n log n)', 'space' => 'O(1)'],
            ], fake()->numberBetween(1, 2)),
        ];
    }
}
