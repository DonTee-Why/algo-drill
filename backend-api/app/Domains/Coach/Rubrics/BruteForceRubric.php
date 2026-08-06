<?php

declare(strict_types=1);

namespace App\Domains\Coach\Rubrics;

final class BruteForceRubric
{
    public const PASS_THRESHOLD = 5;

    /**
     * @return list<array{key: string, max_score: int, expectation: string}>
     */
    public static function items(): array
    {
        return [
            [
                'key' => 'compiles',
                'max_score' => 3,
                'expectation' => 'User code should compile and run.',
            ],
            [
                'key' => 'signature',
                'max_score' => 3,
                'expectation' => 'User code should use the correct function signature.',
            ],
            [
                'key' => 'correctness',
                'max_score' => 3,
                'expectation' => 'User should implement a logically correct naive solution.',
            ],
        ];
    }

    public static function maxScore(): int
    {
        return array_sum(array_column(self::items(), 'max_score'));
    }
}
