<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProblemSignature>
 */
class ProblemSignatureFactory extends Factory
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
            'lang' => fake()->randomElement(\App\Enums\Lang::cases()),
            'function_name' => 'solve'.fake()->word(),
            'params' => [
                ['name' => 'nums', 'type' => 'number[]'],
                ['name' => 'target', 'type' => 'number'],
            ],
            'returns' => [
                'type' => 'number',
            ],
        ];
    }
}
