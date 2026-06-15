<?php

declare(strict_types=1);

namespace App\Domains\Evaluator\Contracts;

use App\Domains\Coach\DTOs\CoachCritiqueResult;
use App\Enums\Stage;
use App\Models\CoachingSession;

interface RubricEvaluator
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, array<string, mixed>>|CoachCritiqueResult
     */
    public function evaluate(Stage $stage, array $payload, CoachingSession $session): array|CoachCritiqueResult;
}
