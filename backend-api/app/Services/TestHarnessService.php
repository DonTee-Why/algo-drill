<?php

namespace App\Services;

use App\Models\CoachingSession;

class TestHarnessService
{
    /**
     * Run the brute force submission through the test harness.
     *
     * This is a minimal stub. Replace with real Piston integration.
     */
    public function runCode(CoachingSession $session, ?string $lang, string $code): array
    {
        return [
            'compiled' => true,
            'signature_ok' => true,
            'tests' => [
                'summary' => [
                    'passed' => 0,
                    'failed' => 0,
                    'total' => 0,
                ],
                'cases' => [],
            ],
            'coach_msg' => null,
        ];
    }
}

