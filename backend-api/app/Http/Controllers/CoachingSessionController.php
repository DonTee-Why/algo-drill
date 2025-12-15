<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\StateMachine\SessionStateMachine;
use App\Enums\Stage;
use App\Exceptions\InvalidSessionStateException;
use App\Http\Requests\SubmitCoachingSessionRequest;
use App\Models\CoachingSession;
use App\Models\Problem;
use App\Services\CoachingSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;
use Symfony\Component\HttpFoundation\JsonResponse;

class CoachingSessionController extends Controller
{
    public function __construct(
        private SessionStateMachine $stateMachine,
        private CoachingSessionService $coachingSessionService
    ) {}

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'problem_id' => ['required', 'string', 'exists:problems,id'],
        ]);

        $session = $this->coachingSessionService->startSession($request->user(), $validated['problem_id']);

        return redirect()->route('sessions.show', $session->id)
            ->with('success', 'Coaching session started successfully!');
    }

    public function submit(SubmitCoachingSessionRequest $request, CoachingSession $session): RedirectResponse
    {
        $this->authorize('submit', $session);

        $payload = $request->validated();

        try {
            $stageResult = $this->coachingSessionService->submitSession($session, $payload);
            return redirect()->route('sessions.show', $session->id)
                ->with('success', 'Submission received successfully!')
                ->with('stageResult', $stageResult);
        } catch (InvalidSessionStateException $e) {
            return redirect()->route('sessions.show', $session->id)
                ->with('error', $e->getMessage());
        } catch (Throwable $e) {
            return redirect()->route('sessions.show', $session->id)
                ->with('error', $e->getMessage());
        }
    }

    public function progress(Request $request, CoachingSession $session): JsonResponse
    {
        $progress = $this->coachingSessionService->getSessionProgress($session);
        return response()->json($progress);
    }
}
