<?php

namespace App\Services;

use App\Enums\Lang;
use App\Models\CoachingSession;
use App\Models\ProblemSignature;
use App\Models\ProblemTest;
use App\Models\TestRun;
use App\Runner\CodeRunnerEngine;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class TestHarnessService
{
    public function __construct(
        private CodeRunnerEngine $codeRunnerEngine
    ) {}

    /**
     * Run the code submission through the test harness.
     *
     * @param  CoachingSession  $session  The coaching session
     * @param  string|null  $lang  The language of the code
     * @param  string  $code  The code to run
     * @param  bool  $isSubmission  Whether this is a formal submission (vs just running tests)
     * @return array The result of the test harness
     */
    public function runCode(CoachingSession $session, ?string $lang, string $code, bool $isSubmission = false): array
    {
        try {
            // Use provided lang, fallback to session's selected_lang, then default to Javascript
            $langToUse = $lang ?? $session->selected_lang ?? 'javascript';

            try {
                $langEnum = Lang::from($langToUse);
            } catch (\ValueError $e) {
                Log::error('Invalid language provided to TestHarnessService', [
                    'provided_lang' => $lang,
                    'session_lang' => $session->selected_lang,
                    'lang_to_use' => $langToUse,
                ]);

                return $this->createErrorResult("Invalid language: $langToUse. Supported languages: javascript, python, php");
            }

            $problem = $session->problem;

            if (! $problem) {
                return $this->createErrorResult('Problem not found for this session');
            }

            $signature = ProblemSignature::query()
                ->where('problem_id', $problem->id)
                ->where('lang', $langEnum)
                ->first();

            if (! $signature) {
                Log::warning('No signature found for language', [
                    'problem_id' => $problem->id,
                    'lang' => $langEnum->value,
                    'provided_lang' => $lang,
                    'session_lang' => $session->selected_lang,
                ]);

                return $this->createErrorResult("No signature found for language: {$langEnum->value}");
            }

            $tests = ProblemTest::query()
                ->where('problem_id', $problem->id)
                ->orderBy('weight')
                ->get();

            if ($tests->isEmpty()) {
                return $this->createErrorResult('No tests found for this problem');
            }

            $signatureOk = $this->checkSignature($code, $signature);

            try {
                $result = $this->codeRunnerEngine->run($langEnum, $signature, $code, $tests);
            } catch (Exception $e) {
                Log::error('CodeRunnerEngine error', [
                    'session_id' => $session->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return $this->createErrorResult($e->getMessage());
            }

            // Persist TestRun
            TestRun::query()->create([
                'coaching_session_id' => $session->id,
                'lang' => $langEnum,
                'source' => $code,
                'result' => [
                    'summary' => $result['testResults']['summary'],
                    'cases' => $result['testResults']['cases'],
                ],
                'cpu_ms' => $result['cpuMs'],
                'mem_kb' => $result['memKb'],
                'stderr_truncated' => $result['stderrTruncated'],
                'is_submission' => $isSubmission,
            ]);

            return [
                'compiled' => $result['compiled'],
                'signature_ok' => $signatureOk,
                'tests' => $result['testResults'],
            ];
        } catch (Exception $e) {
            Log::error('TestHarnessService error', [
                'session_id' => $session->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->createErrorResult('An unexpected error occurred: '.$e->getMessage());
        }
    }

    private function checkSignature(string $code, ProblemSignature $signature): bool
    {
        $functionName = $signature->function_name;

        // Basic check: function name exists in code
        return str_contains($code, $functionName);
    }

    private function createErrorResult(string $message): array
    {
        return [
            'compiled' => false,
            'signature_ok' => false,
            'tests' => [
                'summary' => [
                    'passed' => 0,
                    'failed' => 0,
                    'total' => 0,
                ],
                'cases' => [],
                'error' => $message,
            ],
        ];
    }
}
