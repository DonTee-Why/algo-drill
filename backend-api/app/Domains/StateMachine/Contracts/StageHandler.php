<?php

namespace App\Domains\StateMachine\Contracts;

use App\Domains\StateMachine\DTOs\StageResult;
use App\Models\CoachingSession;

interface StageHandler {
    /**
     * @param CoachingSession $session
     * @param array $coachingSessionPayload
     * @return StageResult
     */
    public function evaluate(CoachingSession $session, array $coachingSessionPayload): StageResult;
}
