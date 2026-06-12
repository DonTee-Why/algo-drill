<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Stage;
use App\Models\CoachingSession;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $activeSessions = CoachingSession::query()
            ->where('user_id', $request->user()->id)
            ->where('state', '!=', Stage::Done)
            ->with(['problem:id,title,slug,difficulty'])
            ->latest('updated_at')
            ->limit(5)
            ->get()
            ->map(fn (CoachingSession $session) => [
                'id' => $session->id,
                'state' => $session->state->value,
                'updated_at' => $session->updated_at,
                'problem' => [
                    'id' => $session->problem->id,
                    'title' => $session->problem->title,
                    'slug' => $session->problem->slug,
                    'difficulty' => $session->problem->difficulty,
                ],
            ]);

        return Inertia::render('Dashboard', [
            'activeSessions' => $activeSessions,
        ]);
    }
}
