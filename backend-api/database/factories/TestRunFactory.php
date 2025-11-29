<?php

namespace Database\Factories;

use App\Enums\Lang;
use App\Models\CoachingSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TestRun>
 */
class TestRunFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $lang = fake()->randomElement(Lang::cases());
        $passed = fake()->numberBetween(5, 10);
        $failed = fake()->numberBetween(0, 3);

        return [
            'coaching_session_id' => CoachingSession::factory(),
            'lang' => $lang,
            'source' => fake()->optional()->text(500),
            'result' => [
                'passed' => $passed,
                'failed' => $failed,
                'total' => $passed + $failed,
                'cases' => fake()->randomElements(['test_1', 'test_2', 'test_3', 'test_4', 'test_5'], $passed),
            ],
            'cpu_ms' => fake()->numberBetween(10, 500),
            'mem_kb' => fake()->numberBetween(1024, 10240),
            'stderr_truncated' => fake()->boolean(10),
        ];
    }
}
