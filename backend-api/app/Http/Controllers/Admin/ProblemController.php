<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProblemRequest;
use App\Http\Requests\UpdateProblemRequest;
use App\Models\Problem;
use App\Services\ProblemService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProblemController extends Controller
{
    public function __construct(
        private readonly ProblemService $problemService
    ) {}

    public function index(): Response
    {
        $problems = Problem::orderBy('created_at', 'desc')->paginate(15);

        return Inertia::render('Admin/Problems/Index', [
            'problems' => $problems,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Problems/Edit', [
            'problem' => null,
        ]);
    }

    public function store(StoreProblemRequest $request): RedirectResponse
    {
        $this->problemService->createProblem($request->validated());

        return redirect()->route('admin.problems.index')
            ->with('success', 'Problem created successfully.');
    }

    public function edit(Problem $problem): Response
    {
        $problem->load(['signatures', 'tests']);

        return Inertia::render('Admin/Problems/Edit', [
            'problem' => $problem,
        ]);
    }

    public function update(UpdateProblemRequest $request, Problem $problem): RedirectResponse
    {
        $this->problemService->updateProblem($problem, $request->validated());

        return redirect()->route('admin.problems.index')
            ->with('success', 'Problem updated successfully.');
    }

    public function destroy(Problem $problem): RedirectResponse
    {
        $problem->delete();

        return redirect()->route('admin.problems.index')
            ->with('success', 'Problem deleted successfully.');
    }
}
