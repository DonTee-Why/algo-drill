<?php

namespace App\Domains\Evaluator;

use App\Domains\Evaluator\Contracts\RubricEvaluator;
use App\Enums\Stage;
use App\Models\CoachingSession;

class CoachEvaluator implements RubricEvaluator
{
    public function evaluate(Stage $stage, array $payload, CoachingSession $session): array
    {
        return match ($stage) {
            Stage::Clarify => $this->clarify($payload, $session),
            Stage::BruteForce => $this->bruteForce($payload),
            Stage::Optimize => $this->optimize($payload),
            Stage::Done => $this->done($payload),
        };
    }

    public static function clarify(array $payload, CoachingSession $session): array
    {
        $scores = [
            'correctness' => [
                'score' => 0,
                'by' => 'coach',
            ],
        ];

        return $scores;
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
