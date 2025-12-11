<?php

namespace App\Domains\StateMachine\StageHandlers;

use App\Domains\StateMachine\Contracts\StageHandler;
use App\Domains\StateMachine\DTOs\StageResult;
use App\Models\CoachingSession;
use App\Enums\Stage;

class BruteForceStage implements StageHandler {
    /**
     * Evaluate the brute force stage
     *
     * @param CoachingSession $session
     * @param array $payload
     * @return StageResult
     */
    public function evaluate(CoachingSession $session, array $payload): StageResult {
        $rubricScores = [
            'brute_force' => [
                'score' => 10,
                'by' => 'auto',
            ],
        ];
        $testResults = [];
        $coachMsg = null;
        $passed = true;
        return new StageResult(
            $rubricScores,
            $passed,
            Stage::BruteForce->next(),
            $testResults,
            $coachMsg
        );
    }
}