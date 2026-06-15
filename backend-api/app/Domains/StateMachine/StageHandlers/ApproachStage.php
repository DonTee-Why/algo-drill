<?php

namespace App\Domains\StateMachine\StageHandlers;

use App\Domains\StateMachine\Contracts\StageHandler;
use App\Domains\StateMachine\DTOs\StageResult;
use App\Enums\Stage;
use App\Models\CoachingSession;

class ApproachStage implements StageHandler
{
    /**
     * Evaluate the approach stage
     */
    public function evaluate(CoachingSession $session, array $coachingSessionPayload): StageResult
    {
        $rubricScores = [
            'approach' => [
                'score' => 10,
                'by' => 'auto',
            ],
        ];
        $testResults = [];
        $coachMsg = null;
        $passed = true;

        return new StageResult(
            stage: Stage::Approach,
            evaluator: 'auto',
            rubricScores: $rubricScores,
            totalScore: 10,
            passThreshold: 0,
            passed: $passed,
            nextState: Stage::Approach->next(),
            testResults: $testResults,
            coachMsg: $coachMsg,
        );
    }
}
