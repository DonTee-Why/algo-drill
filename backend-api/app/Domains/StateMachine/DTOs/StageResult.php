<?php

namespace App\Domains\StateMachine\DTOs;

use App\Enums\Stage;

final class StageResult
{
    public function __construct(
        public ?Stage $stage,
        public string $evaluator,
        public array $rubricScores,
        public int $totalScore,
        public int $passThreshold,
        public bool $passed,
        public ?Stage $nextState,
        public ?array $testResults = null,
        public ?string $coachMsg = null,
        public ?array $flags = null,
        public ?array $questions = null,
    ) {}
}
