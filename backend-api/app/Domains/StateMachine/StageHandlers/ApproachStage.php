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
     *
     * @param CoachingSession $session
     * @param array $payload
     * @return StageResult
     */
    public function evaluate(CoachingSession $session, array $payload): StageResult
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
            $rubricScores,
            $passed,
            Stage::Approach->next(),
            $testResults,
            $coachMsg
        );
    }
}
