<?php

declare(strict_types=1);

namespace App\Domains\Coach\DTOs;

use App\Enums\Stage;

final class CoachCritiqueRequest
{
    /**
     * @param  list<array{key: string, max_score: int, expectation: string}>  $rubric
     * @param  array<string, mixed>  $submission
     * @param  array<string, mixed>  $autoSignals
     */
    public function __construct(
        public string $sessionId,
        public Stage $stage,
        public array $rubric,
        public ProblemContext $problemContext,
        public array $submission,
        public array $autoSignals,
        public CoachConstraints $coachConstraints,
    ) {}

    /**
     * @return array{
     *     session_id: string,
     *     stage: string,
     *     rubric: list<array{key: string, max_score: int, expectation: string}>,
     *     problem_context: array{
     *         title: string,
     *         description: string,
     *         tags: list<string>|array<int, string>,
     *         constraints: list<string>|array<int, string>,
     *         difficulty: string,
     *         signature: array{function_name: string, params: mixed, returns: mixed}|null
     *     },
     *     submission: array<string, mixed>,
     *     auto_signals: array<string, mixed>,
     *     coach_constraints: array{
     *         no_code: bool,
     *         no_solution_reveal: bool,
     *         feedback_style: string,
     *         max_questions: int,
     *         max_tokens: int
     *     }
     * }
     */
    public function toArray(): array
    {
        return [
            'session_id' => $this->sessionId,
            'stage' => $this->stage->value,
            'rubric' => $this->rubric,
            'problem_context' => $this->problemContext->toArray(),
            'submission' => $this->submission,
            'auto_signals' => $this->autoSignals,
            'coach_constraints' => $this->coachConstraints->toArray(),
        ];
    }
}
