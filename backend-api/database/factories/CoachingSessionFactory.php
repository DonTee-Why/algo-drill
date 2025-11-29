<?php

namespace Database\Factories;

use App\Enums\Stage;
use App\Models\Problem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CoachingSession>
 */
class CoachingSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'problem_id' => Problem::factory(),
            'state' => fake()->randomElement(Stage::cases()),
            'scores' => [
                'clarify' => ['auto' => fake()->numberBetween(0, 3), 'coach' => fake()->numberBetween(0, 3)],
                'brute_force' => ['auto' => fake()->numberBetween(0, 3), 'coach' => fake()->numberBetween(0, 3)],
            ],
            'hints_used' => [
                'clarify' => fake()->numberBetween(0, 3),
                'brute_force' => fake()->numberBetween(0, 2),
            ],
            'timers' => [
                'clarify' => fake()->numberBetween(60000, 300000),
                'brute_force' => fake()->numberBetween(120000, 600000),
            ],
            'revealed_langs' => fake()->randomElements(['javascript', 'python', 'php'], fake()->numberBetween(0, 3)),
            'revealed_at' => fake()->optional()->dateTimeBetween('-1 week', 'now'),
        ];
    }
}
