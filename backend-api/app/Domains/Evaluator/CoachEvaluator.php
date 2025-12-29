<?php

namespace App\Domains\Evaluator;

use App\Domains\Evaluator\Contracts\RubricEvaluator;
use App\Enums\Stage;

class CoachEvaluator implements RubricEvaluator
{
    public function evaluate(Stage $stage, array $payload): array
    {
        return match ($stage) {
            Stage::BruteForce => $this->bruteForce($payload),
            Stage::Optimize => $this->optimize($payload),
            Stage::Done => $this->done($payload),
        };
    }

    private static function bruteForce(array $payload): array
    {
        $runner = $payload['runner'] ?? [];

        $correctnessScore = ($runner['tests']['summary']['failed'] ?? 1) === 0 ? 3 : 0;

        $scores = [
            'correctness' => [
                'score' => $correctnessScore,
                'by' => 'coach',
            ],
            'coach_msg' => $runner['coach_msg'] ?? null,
        ];

        return $scores;
    }

    private static function optimize(array $payload): array
    {
        return [];
    }

    private static function done(array $payload): array
    {
        return [];
    }
}
