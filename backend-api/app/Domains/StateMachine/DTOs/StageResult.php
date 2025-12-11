<?php

namespace App\Domains\StateMachine\DTOs;

use App\Enums\Stage;

final class StageResult {
    public function __construct(
        public array $rubricScores,
        public bool  $passed,
        public ?Stage $nextState,
        public ?array $testResults = null,
        public ?string $coachMsg = null
    ) {}
}