<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Problem>
 */
class ProblemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'title' => rtrim($title, '.'),
            'slug' => \Illuminate\Support\Str::slug($title),
            'difficulty' => fake()->randomElement(['Easy', 'Medium', 'Hard']),
            'tags' => fake()->randomElements(['Array', 'String', 'Hash Table', 'Dynamic Programming', 'Math', 'Sorting', 'Greedy', 'Tree', 'Graph', 'Binary Search'], fake()->numberBetween(1, 4)),
            'constraints' => [
                '1 <= n <= 10^4',
                '0 <= nums[i] <= 100',
                'All inputs are valid',
            ],
            'description_md' => "# Problem Description\n\n".fake()->paragraphs(3, true)."\n\n## Examples\n\n**Example 1:**\n```\nInput: [1,2,3]\nOutput: [3,2,1]\n```",
            'is_premium' => fake()->boolean(20),
        ];
    }
}
