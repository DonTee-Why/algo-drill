<?php

namespace App\Domains\StateMachine\StageHandlers;

use App\Domains\Evaluator\AutoEvaluator;
use App\Domains\StateMachine\Contracts\StageHandler;
use App\Domains\StateMachine\DTOs\StageResult;
use App\Enums\Stage;
use App\Models\CoachingSession;
use Exception;
use Illuminate\Support\Facades\Log;

class ClarifyStage implements StageHandler
{
    public const PASS_THRESHOLD = 7;

    /**
     * Evaluate the clarify stage
     *
     * @param CoachingSession $session
     * @param array $payload
     * @return StageResult
     */
    public function evaluate(CoachingSession $session, array $payload): StageResult
    {
        try {
            $rubricScores = AutoEvaluator::clarify($payload);

            // Calculate total score (max 12, pass threshold >= 7)
            $totalScore = $rubricScores['total'];
            $passed = $totalScore >= self::PASS_THRESHOLD;

            unset($rubricScores['total']);

            $testResults = [];
            $coachMsg = $passed ? null : 'Please provide more detail in your clarifications.';

            return new StageResult(
                stage: Stage::Clarify,
                evaluator: 'auto',
                rubricScores: $rubricScores,
                totalScore: (string) $totalScore,
                passThreshold: (string) self::PASS_THRESHOLD,
                passed: $passed,
                nextState: $passed ? Stage::Clarify->next() : Stage::Clarify,
                testResults: $testResults,
                coachMsg: $coachMsg,
            );
        } catch (Exception $e) {
            Log::error(
                'Error evaluating clarify stage: '.$e->getMessage(),
                [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTrace(),
                ]
            );

            return new StageResult(
                stage: Stage::Clarify,
                evaluator: 'auto',
                rubricScores: [],
                totalScore: '0',
                passThreshold: (string) self::PASS_THRESHOLD,
                passed: false,
                nextState: Stage::Clarify,
                testResults: [],
                coachMsg: 'An unexpected error occurred. Please try again.',
            );
        }
    }
}
