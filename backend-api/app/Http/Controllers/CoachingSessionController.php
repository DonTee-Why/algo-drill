<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Stage;
use App\Models\CoachingSession;
use App\Models\Problem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CoachingSessionController extends Controller
{
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
}
