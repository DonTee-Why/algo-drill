<?php

declare(strict_types=1);

namespace App\Domains\StateMachine;

use App\Domains\StateMachine\DTOs\StageResult;
use App\Domains\StateMachine\Factory\StateHandlerFactory;
use App\Enums\Stage;
use App\Exceptions\InvalidSessionStateException;
use App\Models\CoachingSession;
use Illuminate\Support\Facades\DB;

class SessionStateMachine
{
    public function __construct(private StateHandlerFactory $stateHandlerFactory) {}

    /**
     * Process a submission for the current stage
     *
     * @param CoachingSession $session
     * @param array $coachingSessionPayload
     * @return StageResult
     * @throws InvalidSessionStateException
     */
    public function process(CoachingSession $session, array $coachingSessionPayload): StageResult
    {
        $this->validateSessionState($session);
        $stageResult = $this->evaluate($session, $coachingSessionPayload);

        $this->updateSessionState(
            $session,
            $stageResult,
            $coachingSessionPayload
        );
        return $stageResult;
    }

    /**
     * Evaluate a submission for the current stage
     *
     * @throws InvalidSessionStateException
     */
    protected function evaluate(CoachingSession $session, array $coachingSessionPayload): StageResult
    {
        $currentStage = $session->state;
        $stateHandler = $this->stateHandlerFactory->for($currentStage);

        return $stateHandler->evaluate($session, $coachingSessionPayload);
    }

    /**
     * Validate that the session is in a valid state to accept submissions
     *
     * @throws InvalidSessionStateException
     */
    protected function validateSessionState(CoachingSession $session): void
    {
        // Check 1: Session must not be DONE
        if ($session->state === Stage::Done) {
            throw InvalidSessionStateException::sessionCompleted();
        }

        // Additional validations can be added here later:
        // - Check if session is expired
        // - Check if user has exceeded attempt limits
        // - etc.
    }

    /**
     * Update the session state
     *
     * @param CoachingSession $session
     * @param StageResult $stageResult
     * @param array $coachingSessionPayload
     * @return void
     */
    protected function updateSessionState(CoachingSession $session, StageResult $stageResult, array $coachingSessionPayload = []): void
    {
        DB::transaction(function () use ($session, $stageResult, $coachingSessionPayload) {
            $session->attempts()->create([
                'stage' => $session->state,
                'payload' => $coachingSessionPayload,
                'coach_msg' => $stageResult->coachMsg,
                'rubric_scores' => $stageResult->rubricScores,
            ]);

            if ($stageResult->passed && $stageResult->nextState) {
                $session->state = $stageResult->nextState;
            }

            $session->saveOrFail();
        });
    }
}
