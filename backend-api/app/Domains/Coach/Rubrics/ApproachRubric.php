<?php

declare(strict_types=1);

namespace App\Domains\Coach\Rubrics;

final class ApproachRubric
{
    public const PASS_THRESHOLD = 4;

    /**
     * @return list<array{key: string, max_score: int, expectation: string}>
     */
    public static function items(): array
    {
        return [
            [
                'key' => 'strategy',
                'max_score' => 2,
                'expectation' => 'User should state a clear high-level algorithmic idea.',
            ],
            [
                'key' => 'justification',
                'max_score' => 2,
                'expectation' => 'User should explain why the approach solves the problem.',
            ],
            [
                'key' => 'complexity',
                'max_score' => 2,
                'expectation' => 'User should state rough time and space complexity.',
            ],
        ];
    }

    public static function maxScore(): int
    {
        return array_sum(array_column(self::items(), 'max_score'));
    }
}
