<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Problem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProblemController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Problem::query();

        // Search by title
        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }

        // Filter by difficulty
        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }

        // Filter by tags (checking if any tag matches)
        if ($request->filled('tags') && is_array($request->tags)) {
            foreach ($request->tags as $tag) {
                $query->whereJsonContains('tags', $tag);
            }
        }

        $problems = $query->orderBy('created_at', 'desc')->paginate(15);

        return Inertia::render('Problems/Index', [
            'problems' => $problems,
            'filters' => [
                'search' => $request->search,
                'difficulty' => $request->difficulty,
                'tags' => $request->tags,
            ],
        ]);
    }

    public function fetchProblems(Request $request): JsonResponse
    {
        $query = Problem::query();

        // Search by title
        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }

        // Filter by difficulty
        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }

        // Filter by tags (checking if any tag matches)
        if ($request->filled('tags') && is_array($request->tags)) {
            foreach ($request->tags as $tag) {
                $query->whereJsonContains('tags', $tag);
            }
        }

        $problems = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json($problems);
    }

    public function show(Problem $problem): Response
    {
        $problem->load('signatures');

        return Inertia::render('Problems/Show', [
            'problem' => $problem,
        ]);
    }
}
