<?php

namespace App\Services;

use App\Domains\StateMachine\DTOs\StageResult;
use App\Domains\StateMachine\SessionStateMachine;
use App\Enums\Stage;
use App\Exceptions\InvalidSessionStateException;
use App\Models\CoachingSession;
use App\Models\Problem;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;

class CoachingSessionService
{
    public function __construct(private SessionStateMachine $stateMachine) {}

    public function startSession(User $user, string $problem_id): CoachingSession
    {
        $problem = Problem::query()->findOrFail($problem_id);

        $defaultLang = $user->preferred_languages[0] ?? 'javascript';

        $session = CoachingSession::query()->create([
            'user_id' => $user->id,
            'problem_id' => $problem->id,
            'state' => Stage::Clarify,
            'selected_lang' => $defaultLang,
            'scores' => [],
            'hints_used' => [],
            'timers' => [],
            'revealed_langs' => [],
        ]);

        return $session;
    }

    public function submitSession(CoachingSession $session, array $payload): StageResult
    {
        try {
            $stageResult = $this->stateMachine->process($session, $payload);

            return $stageResult;
        } catch (InvalidSessionStateException $e) {
            Log::error(
                'Invalid session state exception: '.$e->getMessage(),
                ['error' => $e->getTrace()]
            );
            throw $e;
        } catch (Exception $e) {
            Log::error('Exception: '.$e->getMessage(), ['error' => $e->getTrace()]);
            throw new Exception('An unexpected error occurred. Please try again.');
        }
    }

    public function getSessionProgress(CoachingSession $session): array
    {
        return [
            'timeline' => $session->attempts,
            'scoresByStage' => $session->scores,
            'hintsUsed' => $session->hints_used,
            'metrics' => $session->metricSnapshots,
            'reveal' => [
                'eligible' => false,
                'revealed' => false,
                'availableLangs' => [],
            ],
        ];
    }
}
