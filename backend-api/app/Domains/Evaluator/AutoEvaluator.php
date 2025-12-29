<?php

namespace App\Domains\Evaluator;

use App\Domains\Evaluator\Contracts\RubricEvaluator;
use App\Enums\Stage;

final class AutoEvaluator implements RubricEvaluator
{
    public function evaluate(Stage $stage, array $payload): array
    {
        return match ($stage) {
            Stage::Clarify => $this->clarify($payload),
            Stage::Approach => $this->approach($payload),
            Stage::Pseudocode => $this->pseudocode($payload),
            Stage::BruteForce => $this->bruteForce($payload),
            Stage::Optimize => $this->optimize($payload),
            Stage::Done => $this->done($payload),
        };
    }

    public static function clarify(array $payload): array
    {
        $scores = [
            'inputs_outputs' => [
                'score' => self::scoreTextPresence($payload['inputs_outputs'] ?? ''),
                'by' => 'auto',
            ],
            'constraints' => [
                'score' => self::scoreTextPresence($payload['constraints'] ?? ''),
                'by' => 'auto',
            ],
            'examples' => [
                'score' => self::scoreExamples($payload['examples'] ?? ''),
                'by' => 'auto',
            ],
        ];

        $scores['total'] = array_sum(array_column(
            $scores,
            'score'
        ));

        return $scores;
    }

    private static function approach(array $payload): array
    {
        return [];
    }
    private static function pseudocode(array $payload): array
    {
        return [];
    }
    public static function bruteForce(array $payload): array
    {
        $runner = $payload['runner'] ?? [];

        $compilesScore = ($runner['compiled'] ?? false) ? 3 : 0;
        $signatureScore = ($runner['signature_ok'] ?? false) ? 3 : 0;

        $allPassed = ($runner['tests']['summary']['failed'] ?? 1) === 0
            && ($runner['tests']['summary']['total'] ?? 0) > 0;
        $anyPassed = ($runner['tests']['summary']['passed'] ?? 0) > 0;

        $scores = [
            'compiles' => [
                'score' => $compilesScore,
                'by' => 'auto',
            ],
            'signature' => [
                'score' => $signatureScore,
                'by' => 'auto',
            ],
        ];

        $scores['total'] = array_sum(array_column(
            $scores,
            'score'
        ));

        return $scores;
    }
    public static function optimize(array $payload): array
    {
        return [];
    }
    public static function done(array $payload): array
    {
        return [];
    }

    /**
     * Score text presence (0-3)
     * 0: empty, 1: very short/weak, 2: moderate, 3: good length and content
     *
     * @param string $text
     * @return int
     */
    private static function scoreTextPresence(string $text): int
    {
        if (empty($text)) {
            return 0;
        }

        $length = \strlen($text);
        if ($length < 10) {
            return 1;
        }
        if ($length < 30) {
            return 2;
        }

        return 3;
    }

    /**
     * Score examples (0-6)
     * Checks for presence, count (>=2 examples), and edge case mention
     * 
     * @param string $examples
     * @return int
     */
    private static function scoreExamples(string $examples): int
    {
        if (empty($examples)) {
            return 0;
        }

        $score = 0;

        // Basic presence (0-2)
        $length = \strlen($examples);
        if ($length >= 30) {
            $score += 2;
        } elseif ($length >= 10) {
            $score += 1;
        }

        // Count examples (look for patterns like "Example 1", "Example 2", or numbered lists)
        $exampleCount = self::countExamples($examples);
        if ($exampleCount >= 2) {
            $score += 2;
        } elseif ($exampleCount >= 1) {
            $score += 1;
        }

        // Edge case mention (0-2)
        $edgeCaseKeywords = ['edge', 'corner', 'empty', 'null', 'zero', 'negative', 'boundary', 'extreme', 'special'];
        $hasEdgeCase = false;
        foreach ($edgeCaseKeywords as $keyword) {
            if (stripos($examples, $keyword) !== false) {
                $hasEdgeCase = true;
                break;
            }
        }

        if ($hasEdgeCase) {
            $score += 2;
        }

        return min($score, 6);
    }

    /**
     * Count the number of examples in the text
     *
     * @param string $text
     * @return int
     */
    private static function countExamples(string $text): int
    {
        // Look for patterns like "Example 1", "Example 2", or numbered lists
        $patterns = [
            '/example\s+\d+/i',
            '/^\d+[\.\)]\s/m', // Numbered list items
            '/^[-*]\s/m', // Bullet points
        ];

        $maxCount = 0;
        foreach ($patterns as $pattern) {
            $count = preg_match_all($pattern, $text);
            if ($count > $maxCount) {
                $maxCount = $count;
            }
        }

        // If no clear pattern, estimate based on line breaks or semicolons
        if ($maxCount === 0) {
            $lines = explode("\n", $text);
            $nonEmptyLines = array_filter($lines, fn($line) => ! empty(trim($line)));
            $maxCount = min(\count($nonEmptyLines), 3); // Cap at 3 for estimation
        }

        return $maxCount;
    }
}
