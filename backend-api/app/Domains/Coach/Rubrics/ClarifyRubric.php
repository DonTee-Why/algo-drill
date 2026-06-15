<?php

declare(strict_types=1);

namespace App\Domains\Coach\Rubrics;

final class ClarifyRubric
{
    public const PASS_THRESHOLD = 7;

    public const MIN_AUTO_EVALUATOR_SCORE = 4;

    /**
     * @return list<array{key: string, max_score: int, expectation: string}>
     */
    public static function items(): array
    {
        return [
            [
                'key' => 'inputs_outputs',
                'max_score' => 3,
                'expectation' => 'User should identify the inputs and the expected return/output contract.',
            ],
            [
                'key' => 'constraints',
                'max_score' => 3,
                'expectation' => 'User should mention relevant constraints or rules.',
            ],
            [
                'key' => 'examples',
                'max_score' => 6,
                'expectation' => 'User should provide at least two valid examples, including one edge case.',
            ],
        ];
    }

    public static function maxScore(): int
    {
        return array_sum(array_column(self::items(), 'max_score'));
    }
}
