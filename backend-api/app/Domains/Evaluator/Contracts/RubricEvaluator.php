<?php

namespace App\Domains\Evaluator\Contracts;

use App\Enums\Stage;

interface RubricEvaluator
{
    public function evaluate(Stage $stage, array $payload): array;
}
