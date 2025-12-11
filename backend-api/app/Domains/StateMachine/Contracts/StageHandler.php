<?php

namespace App\Domains\StateMachine\Contracts;

use App\Domains\StateMachine\DTOs\StageResult;
use App\Models\CoachingSession;

interface StageHandler {
    /**
     * @param CoachingSession $session
     * @param array $payload
     * @return StageResult
     */
    public function evaluate(CoachingSession $session, array $payload): StageResult;
}
