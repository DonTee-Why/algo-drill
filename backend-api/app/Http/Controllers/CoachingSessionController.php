<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\StateMachine\SessionStateMachine;
use App\Enums\Stage;
use App\Exceptions\InvalidSessionStateException;
use App\Http\Requests\SubmitCoachingSessionRequest;
use App\Models\CoachingSession;
use App\Models\Problem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\JsonResponse;

class CoachingSessionController extends Controller
{
    public function __construct(private SessionStateMachine $stateMachine) {}

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'problem_id' => ['required', 'string', 'exists:problems,id'],
        ]);

        $problem = Problem::query()->findOrFail($validated['problem_id']);

        $session = CoachingSession::query()->create([
            'user_id' => $request->user()->id,
            'problem_id' => $problem->id,
            'state' => Stage::Clarify,
            'scores' => [],
            'hints_used' => [],
            'timers' => [],
            'revealed_langs' => [],
        ]);

        return redirect()->route('sessions.show', $session->id)
            ->with('success', 'Coaching session started successfully!');
    }

    public function submit(SubmitCoachingSessionRequest $request, CoachingSession $session): RedirectResponse
    {
        $this->authorize('submit', $session);

        $payload = $request->validated();

        try {
            $stageResult = $this->stateMachine->process($session, $payload);
        } catch (InvalidSessionStateException $e) {
            Log::error('Invalid session state exception: ' . $e->getMessage(), ['error' => $e->getTrace()]);
            return redirect()->route('sessions.show', $session->id)
                ->with('error', $e->getMessage());
        }

        return redirect()->route('sessions.show', $session->id)
            ->with('success', 'Submission received successfully!')
            ->with('stageResult', $stageResult);
    }

    public function progress(Request $request, CoachingSession $session): JsonResponse
    {
        return response()->json([
            'timeline' => $session->attempts,
            'scoresByStage' => $session->scores,
            'hintsUsed' => $session->hints_used,
            'metrics' => $session->metricSnapshots,
            'reveal' => $session->reveal,
        ]);
    }

    public function reveal(Request $request, CoachingSession $session): JsonResponse
    {
        return response()->json([
            'reveal' => $session->reveal,
        ]);
    }
}
