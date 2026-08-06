<?php

declare(strict_types=1);

namespace App\Domains\Coach\DTOs;

final class CoachConstraints
{
    public function __construct(
        public bool $noCode,
        public bool $noSolutionReveal,
        public string $feedbackStyle,
        public int $maxQuestions,
        public int $maxTokens,
    ) {}

    /**
     * @return array{
     *     no_code: bool,
     *     no_solution_reveal: bool,
     *     feedback_style: string,
     *     max_questions: int,
     *     max_tokens: int
     * }
     */
    public function toArray(): array
    {
        return [
            'no_code' => $this->noCode,
            'no_solution_reveal' => $this->noSolutionReveal,
            'feedback_style' => $this->feedbackStyle,
            'max_questions' => $this->maxQuestions,
            'max_tokens' => $this->maxTokens,
        ];
    }
}
