<?php

declare(strict_types=1);

namespace App\Domains\Coach\DTOs;

final class CoachCritiqueResult
{
    /**
     * @param  array<string, array{score: int, max_score?: int, reason?: string, by: string}>  $scores
     * @param  array<string, bool>  $flags
     * @param  list<string>  $questions
     */
    public function __construct(
        public array $scores,
        public ?string $coachMsg,
        public array $flags,
        public array $questions,
        public bool $available,
    ) {}
}
