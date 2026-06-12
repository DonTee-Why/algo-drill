<?php

namespace App\Runner;

use App\Models\ProblemSignature;
use Illuminate\Support\Collection;

interface CodeRunnerInterface
{
    public function generateRunnerCode(ProblemSignature $signature, string $userCode, array|Collection $tests): string;

    public function generateTestCases(ProblemSignature $signature, array|Collection $tests): string;

    public function getMainFileName(): string;

    public function getPistonLanguage(): string;
}
