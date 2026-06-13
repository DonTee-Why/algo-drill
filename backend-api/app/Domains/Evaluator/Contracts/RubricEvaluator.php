<?php

namespace App\Domains\Evaluator\Contracts;

use App\Enums\Stage;
use App\Models\CoachingSession;

interface RubricEvaluator
{
    public function evaluate(Stage $stage, array $payload, CoachingSession $session): array;
}
