<?php

declare(strict_types=1);

namespace App\Domains\Coach\Rubrics;

final class OptimizeRubric
{
    public const PASS_THRESHOLD = 4;

    /**
     * @return list<array{key: string, max_score: int, expectation: string}>
     */
    public static function items(): array
    {
        return [
            [
                'key' => 'optimization',
                'max_score' => 2,
                'expectation' => 'User should implement a better algorithm than the brute-force solution.',
            ],
            [
                'key' => 'complexity_target',
                'max_score' => 1,
                'expectation' => 'User should achieve an improved Big-O complexity.',
            ],
            [
                'key' => 'technique',
                'max_score' => 1,
                'expectation' => 'User should explain the optimization technique used.',
            ],
            [
                'key' => 'tradeoffs',
                'max_score' => 2,
                'expectation' => 'User should articulate time/space tradeoffs.',
            ],
        ];
    }

    public static function maxScore(): int
    {
        return array_sum(array_column(self::items(), 'max_score'));
    }
}
