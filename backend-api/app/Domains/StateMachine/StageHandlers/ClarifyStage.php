<?php

namespace App\Domains\StateMachine\StageHandlers;

use App\Domains\Evaluator\AutoEvaluator;
use App\Domains\StateMachine\Contracts\StageHandler;
use App\Domains\StateMachine\DTOs\StageResult;
use App\Enums\Stage;
use App\Models\CoachingSession;

class ClarifyStage implements StageHandler
{
    /**
     * Evaluate the clarify stage
     *
     * @param CoachingSession $session
     * @param array $payload
     * @return StageResult
     */
    public function evaluate(CoachingSession $session, array $payload): StageResult
    {
        $rubricScores = AutoEvaluator::clarify($payload);

        // Calculate total score (max 12, pass threshold >= 7)
        $totalScore = $rubricScores['total'];
        $passed = $totalScore >= 7;

        unset($rubricScores['total']);

        $testResults = [];
        $coachMsg = $passed ? null : 'Please provide more detail in your clarifications.';

        return new StageResult(
            $rubricScores,
            $passed,
            $passed ? Stage::Clarify->next() : Stage::Clarify,
            $testResults,
            $coachMsg
        );
    }
}
