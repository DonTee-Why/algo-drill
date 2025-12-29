<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\StateMachine\SessionStateMachine;
use App\Enums\Stage;
use App\Exceptions\InvalidSessionStateException;
use App\Http\Requests\SubmitCoachingSessionRequest;
use App\Models\CoachingSession;
use App\Services\CoachingSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

class CoachingSessionController extends Controller
{
    public function __construct(
        private SessionStateMachine $stateMachine,
        private CoachingSessionService $coachingSessionService
    ) {}

    public function index(Request $request): Response
    {
        $sessions = CoachingSession::query()
            ->where('user_id', $request->user()->id)
            ->with(['problem:id,title,slug,difficulty'])
            ->latest()
            ->paginate(20);

        return Inertia::render('Sessions/Index', [
            'sessions' => [
                'data' => $sessions->map(function ($session) {
                    return [
                        'id' => $session->id,
                        'state' => $session->state->value,
                        'created_at' => $session->created_at,
                        'updated_at' => $session->updated_at,
                        'problem' => [
                            'id' => $session->problem->id,
                            'title' => $session->problem->title,
                            'slug' => $session->problem->slug,
                            'difficulty' => $session->problem->difficulty,
                        ],
                    ];
                }),
                'current_page' => $sessions->currentPage(),
                'last_page' => $sessions->lastPage(),
                'per_page' => $sessions->perPage(),
                'total' => $sessions->total(),
            ],
        ]);
    }

    public function show(Request $request, CoachingSession $session): Response
    {
        $this->authorize('view', $session);

        $session->load([
            'problem.signatures',
            'problem.tests',
            'attempts' => function ($query) {
                $query->latest();
            },
            'testRuns' => function ($query) {
                $query->latest()->limit(1);
            },
        ]);

        // Calculate stage progress
        $stages = Stage::cases();
        $stageProgress = [];
        $currentStageIndex = array_search($session->state, $stages);

        foreach ($stages as $index => $stage) {
            $stageAttempts = $session->attempts->where('stage', $stage);
            $totalAttempts = $stageAttempts->count();

            // A stage is completed if the session has moved past it
            $isCompleted = $index < $currentStageIndex;
            // A stage is current if it's the current stage
            $isCurrent = $index === $currentStageIndex;
            // A stage is locked if it's after the current stage
            $isLocked = $index > $currentStageIndex;

            // For completed stages, count how many attempts were needed
            // For current stage, show current progress
            $completedAttempts = $isCompleted ? $totalAttempts : 0;

            $stageProgress[] = [
                'stage' => $stage->value,
                'label' => $stage->name,
                'isCurrent' => $isCurrent,
                'isLocked' => $isLocked,
                'isCompleted' => $isCompleted,
                'totalAttempts' => $totalAttempts,
                'completedAttempts' => $completedAttempts,
            ];
        }

        // Get latest test run results
        $latestTestRun = $session->testRuns->first();
        $testResults = null;
        if ($latestTestRun && $latestTestRun->result) {
            $testResults = [
                'summary' => [
                    'passed' => $latestTestRun->result['passed'] ?? 0,
                    'failed' => $latestTestRun->result['failed'] ?? 0,
                    'total' => $latestTestRun->result['total'] ?? 0,
                ],
                'cases' => $latestTestRun->result['cases'] ?? [],
            ];
        }

        // Get latest attempt for current stage
        $latestAttempt = $session->attempts
            ->where('stage', $session->state)
            ->first();

        // Get latest attempt for each completed stage (for viewing past stages)
        $stageAttempts = [];
        foreach ($stages as $index => $stage) {
            if ($index < $currentStageIndex) {
                // Stage is completed, get the latest passing attempt
                $stageAttempt = $session->attempts
                    ->where('stage', $stage)
                    ->sortByDesc('created_at')
                    ->first();

                if ($stageAttempt) {
                    $stageAttempts[$stage->value] = [
                        'payload' => $stageAttempt->payload,
                        'coach_msg' => $stageAttempt->coach_msg,
                        'rubric_scores' => $stageAttempt->rubric_scores,
                        'created_at' => $stageAttempt->created_at,
                    ];
                }
            }
        }

        return Inertia::render('Sessions/Show', [
            'session' => [
                'id' => $session->id,
                'state' => $session->state->value,
                'selected_lang' => $session->selected_lang,
                'created_at' => $session->created_at,
            ],
            'problem' => [
                'id' => $session->problem->id,
                'title' => $session->problem->title,
                'difficulty' => $session->problem->difficulty,
                'tags' => $session->problem->tags,
                'constraints' => $session->problem->constraints,
                'description_md' => $session->problem->description_md,
                'signatures' => $session->problem->signatures->map(function ($sig) {
                    return [
                        'lang' => $sig->lang->value,
                        'function_name' => $sig->function_name,
                        'params' => $sig->params,
                        'returns' => $sig->returns,
                    ];
                }),
            ],
            'stageProgress' => $stageProgress,
            'testResults' => $testResults,
            'latestAttempt' => $latestAttempt ? [
                'payload' => $latestAttempt->payload,
                'coach_msg' => $latestAttempt->coach_msg,
                'rubric_scores' => $latestAttempt->rubric_scores,
                'created_at' => $latestAttempt->created_at,
            ] : null,
            'stageAttempts' => $stageAttempts,
            'attempts' => $session->attempts->map(function ($attempt) use ($session) {
                // Determine if attempt passed by checking if session moved to next stage after this attempt
                $attemptStageIndex = array_search($attempt->stage, Stage::cases());
                $currentStageIndex = array_search($session->state, Stage::cases());
                $passed = $attemptStageIndex < $currentStageIndex;

                return [
                    'id' => $attempt->id,
                    'stage' => $attempt->stage->value,
                    'coach_msg' => $attempt->coach_msg,
                    'rubric_scores' => $attempt->rubric_scores,
                    'passed' => $passed,
                    'created_at' => $attempt->created_at,
                ];
            })->sortByDesc('created_at')->values(),
        ]);
    }

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

        $validated = $request->validated();
        $payload = $validated['payload'] ?? [];

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

    public function updateLanguage(Request $request, CoachingSession $session): RedirectResponse
    {
        $this->authorize('update', $session);

        $validated = $request->validate([
            'lang' => ['required', 'string', 'in:javascript,python,php'],
        ]);

        $session->update([
            'selected_lang' => $validated['lang'],
        ]);

        return redirect()->back();
    }
}
