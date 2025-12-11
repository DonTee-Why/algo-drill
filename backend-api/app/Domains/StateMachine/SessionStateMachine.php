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
     * @param array $payload
     * @return StageResult
     * @throws InvalidSessionStateException
     */
    public function process(CoachingSession $session, array $payload): StageResult
    {
        $this->validateSessionState($session);
        $stageResult = $this->evaluate($session, $payload);

        $this->updateSessionState(
            $session,
            $stageResult,
            $payload
        );
        return $stageResult;
    }

    /**
     * Evaluate a submission for the current stage
     *
     * @throws InvalidSessionStateException
     */
    protected function evaluate(CoachingSession $session, array $payload): StageResult
    {
        $stage = $session->state;
        $stateHandler = $this->stateHandlerFactory->for($stage);

        return $stateHandler->evaluate($session, $payload);
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
     * @param array $payload
     * @return void
     */
    protected function updateSessionState(CoachingSession $session, StageResult $stageResult, array $payload = []): void
    {
        DB::transaction(function () use ($session, $stageResult, $payload) {
            $session->attempts()->create([
                'stage' => $session->state,
                'payload' => $payload,
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
