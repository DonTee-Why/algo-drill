<?php

namespace App\Domains\StateMachine\StageHandlers;

use App\Domains\StateMachine\Contracts\StageHandler;
use App\Domains\StateMachine\DTOs\StageResult;
use App\Enums\Stage;
use App\Models\CoachingSession;

class PseudocodeStage implements StageHandler
{
    /**
     * Evaluate the pseudocode stage
     */
    public function evaluate(CoachingSession $session, array $payload): StageResult
    {
        $rubricScores = [
            'pseudocode' => [
                'score' => 10,
                'by' => 'auto',
            ],
        ];
        $testResults = [];
        $coachMsg = null;
        $passed = true;

        return new StageResult(
            stage: Stage::Pseudocode,
            evaluator: 'auto',
            rubricScores: $rubricScores,
            totalScore: '10',
            passThreshold: '0',
            passed: $passed,
            nextState: Stage::Pseudocode->next(),
            testResults: $testResults,
            coachMsg: $coachMsg,
        );
    }
}
