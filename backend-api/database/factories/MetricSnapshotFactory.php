<?php

namespace Database\Factories;

use App\Models\CoachingSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MetricSnapshot>
 */
class MetricSnapshotFactory extends Factory
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
            'time_to_bf' => fake()->numberBetween(60, 600),
            'passes_to_ac' => fake()->numberBetween(1, 10),
            'edges_missed_pct' => fake()->numberBetween(0, 50),
            'hint_count' => fake()->numberBetween(0, 5),
            'relapse_flags' => fake()->optional()->randomElements(['missed_edge_case', 'incorrect_complexity', 'syntax_error'], fake()->numberBetween(1, 2)),
        ];
    }
}
