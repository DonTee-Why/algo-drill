<?php

namespace Database\Factories;

use App\Enums\Stage;
use App\Models\CoachingSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attempt>
 */
class AttemptFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'coaching_session_id' => CoachingSession::factory(),
            'stage' => fake()->randomElement(Stage::cases()),
            'payload' => [
                'user_input' => fake()->paragraphs(2, true),
                'clarifications' => fake()->sentences(3),
            ],
            'coach_msg' => fake()->paragraph(),
            'rubric_scores' => [
                'clarity' => ['score' => fake()->numberBetween(0, 3), 'by' => 'auto'],
                'completeness' => ['score' => fake()->numberBetween(0, 3), 'by' => 'coach'],
            ],
        ];
    }
}
