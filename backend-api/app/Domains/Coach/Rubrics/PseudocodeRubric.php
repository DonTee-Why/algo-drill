<?php

declare(strict_types=1);

namespace App\Domains\Coach\Rubrics;

final class PseudocodeRubric
{
    public const PASS_THRESHOLD = 6;

    /**
     * @return list<array{key: string, max_score: int, expectation: string}>
     */
    public static function items(): array
    {
        return [
            [
                'key' => 'step_order',
                'max_score' => 3,
                'expectation' => 'User should present steps in a logical order.',
            ],
            [
                'key' => 'bounds',
                'max_score' => 3,
                'expectation' => 'User should make loop/index bounds and termination clear.',
            ],
            [
                'key' => 'edge_handling',
                'max_score' => 3,
                'expectation' => 'User should handle edge cases explicitly.',
            ],
        ];
    }

    public static function maxScore(): int
    {
        return array_sum(array_column(self::items(), 'max_score'));
    }
}
