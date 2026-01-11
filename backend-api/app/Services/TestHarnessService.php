<?php

namespace App\Services;

use App\Enums\Lang;
use App\Models\CoachingSession;
use App\Models\ProblemSignature;
use App\Models\ProblemTest;
use App\Models\TestRun;
use Exception;
use Illuminate\Support\Facades\Log;

class TestHarnessService
{
    public function __construct(
        private PistonClient $pistonClient
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

            // Generate runner code
            $runnerCode = $this->generateRunnerCode($langEnum, $signature, $code, $tests);

            // Map language to Piston language identifier
            $pistonLang = $this->mapLanguageToPiston($langEnum);
            $pistonVersion = '*'; // Use latest version

            // Execute via Piston
            $response = $this->pistonClient->execute(
                language: $pistonLang,
                version: $pistonVersion,
                files: [
                    [
                        'name' => $this->getMainFileName($langEnum),
                        'content' => $runnerCode,
                    ],
                ],
                stdin: '',
                args: [],
                runTimeout: 3000,
                runCpuTime: 3000,
                runMemoryLimit: 128 * 1024 * 1024,
            );

            Log::info('Piston response', ['response' => $response]);

            if (! $response['success']) {
                Log::error('Piston execution failed', [
                    'session_id' => $session->id,
                    'response' => $response,
                ]);

                return $this->createErrorResult($response['message'] ?? 'Execution failed');
            }

            $pistonData = $response['data'] ?? [];

            // Parse runner output
            $testResults = $this->parseRunnerOutput($pistonData, $tests);

            // Check compilation status
            $compiled = $this->checkCompiled($pistonData, $langEnum);
            $signatureOk = $this->checkSignature($code, $signature);

            // Extract CPU and memory from Piston response
            $cpuMs = $pistonData['run']['cpu_time'] ?? null;
            $memKb = isset($pistonData['run']['memory']) ? (int) ($pistonData['run']['memory'] / 1024) : null;
            $stderrTruncated = false;

            // Check if stderr was truncated (Piston limits stdout/stderr)
            if (isset($pistonData['run']['stderr'])) {
                $stderrLength = strlen($pistonData['run']['stderr']);
                // Piston defaults to 1024 char limit for stdout/stderr
                $stderrTruncated = $stderrLength >= 1024;
            }

            // Persist TestRun
            TestRun::query()->create([
                'coaching_session_id' => $session->id,
                'lang' => $langEnum,
                'source' => $code,
                'result' => [
                    'summary' => $testResults['summary'],
                    'cases' => $testResults['cases'],
                ],
                'cpu_ms' => $cpuMs,
                'mem_kb' => $memKb,
                'stderr_truncated' => $stderrTruncated,
                'is_submission' => $isSubmission,
            ]);

            return [
                'compiled' => $compiled,
                'signature_ok' => $signatureOk,
                'tests' => $testResults,
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

    /**
     * Generate runner code that wraps user code and runs tests
     *
     * @param  Lang  $lang  The language of the code
     * @param  ProblemSignature  $signature  The signature of the problem
     * @param  string  $userCode  The user code to run
     * @param  $tests  The tests to run
     * @return string The runner code
     */
    private function generateRunnerCode(Lang $lang, ProblemSignature $signature, string $userCode, $tests): string
    {
        return match ($lang) {
            Lang::Javascript => $this->generateJavaScriptRunner($signature, $userCode, $tests),
            Lang::Python => $this->generatePythonRunner($signature, $userCode, $tests),
            Lang::Php => $this->generatePhpRunner($signature, $userCode, $tests),
        };
    }

    private function generateJavaScriptRunner(ProblemSignature $signature, string $userCode, $tests): string
    {
        $functionName = $signature->function_name;
        $testCases = $this->generateTestCases($signature, $tests, 'javascript');

        return <<<JS
{$userCode}

// Test runner
const results = [];
let passed = 0;
let failed = 0;

function deepEqual(a, b) {
    if (a === b) return true;
    if (a == null || b == null) return false;
    if (typeof a !== typeof b) return false;
    if (typeof a !== 'object') return false;
    
    if (Array.isArray(a) !== Array.isArray(b)) return false;
    
    if (Array.isArray(a)) {
        if (a.length !== b.length) return false;
        for (let i = 0; i < a.length; i++) {
            if (!deepEqual(a[i], b[i])) return false;
        }
        return true;
    }
    
    const keysA = Object.keys(a);
    const keysB = Object.keys(b);
    if (keysA.length !== keysB.length) return false;
    
    for (const key of keysA) {
        if (!keysB.includes(key)) return false;
        if (!deepEqual(a[key], b[key])) return false;
    }
    return true;
}

{$testCases}

console.log(JSON.stringify({
    summary: {
        passed: passed,
        failed: failed,
        total: passed + failed
    },
    cases: results
}));
JS;
    }

    private function generatePythonRunner(ProblemSignature $signature, string $userCode, $tests): string
    {
        $functionName = $signature->function_name;
        $testCases = $this->generateTestCases($signature, $tests, 'python');

        return <<<PY
import json

{$userCode}

# Test runner
results = []
passed = 0
failed = 0

def deep_equal(a, b):
    if a == b:
        return True
    if a is None or b is None:
        return False
    if type(a) != type(b):
        return False
    if isinstance(a, (list, tuple)):
        if len(a) != len(b):
            return False
        return all(deep_equal(x, y) for x, y in zip(a, b))
    if isinstance(a, dict):
        if set(a.keys()) != set(b.keys()):
            return False
        return all(deep_equal(a[k], b[k]) for k in a.keys())
    return False

{$testCases}

print(json.dumps({
    'summary': {
        'passed': passed,
        'failed': failed,
        'total': passed + failed
    },
    'cases': results
}))
PY;
    }

    private function generatePhpRunner(ProblemSignature $signature, string $userCode, $tests): string
    {
        $functionName = $signature->function_name;
        $testCases = $this->generateTestCases($signature, $tests, 'php');

        return <<<PHP
<?php
{$userCode}

// Test runner
\$results = [];
\$passed = 0;
\$failed = 0;

function deep_equal(\$a, \$b) {
    if (\$a === \$b) return true;
    if (\$a === null || \$b === null) return false;
    if (gettype(\$a) !== gettype(\$b)) return false;
    
    if (is_array(\$a)) {
        if (count(\$a) !== count(\$b)) return false;
        foreach (\$a as \$key => \$val) {
            if (!isset(\$b[\$key]) || !deep_equal(\$val, \$b[\$key])) {
                return false;
            }
        }
        return true;
    }
    
    return false;
}

{$testCases}

echo json_encode([
    'summary' => [
        'passed' => \$passed,
        'failed' => \$failed,
        'total' => \$passed + \$failed
    ],
    'cases' => \$results
]);
PHP;
    }

    private function generateTestCases(ProblemSignature $signature, $tests, string $lang): string
    {
        $functionName = $signature->function_name;
        $cases = [];

        // Check if this is an in-place modification function (void return type)
        $isVoidReturn = isset($signature->returns['type']) && $signature->returns['type'] === 'void';

        foreach ($tests as $index => $test) {
            $input = $this->formatValue($test->input, $lang);
            $expected = $this->formatValue($test->expected, $lang);
            $inputArgs = $this->formatInputArgs($test->input, $signature, $lang);

            // Get the first param name for in-place modifications
            $firstParamName = $signature->params[0]['name'] ?? 'arg0';

            if ($lang === 'javascript') {
                if ($isVoidReturn) {
                    // For in-place functions, compare the modified input array
                    $cases[] = <<<JS
try {
    const {$firstParamName} = structuredClone({$input}[0]);
    {$functionName}({$firstParamName});
    const isEqual = deepEqual({$firstParamName}, {$expected});
    if (isEqual) {
        passed++;
        results.push({
            status: 'passed',
            input: {$input},
            expected: {$expected},
            got: {$firstParamName}
        });
    } else {
        failed++;
        results.push({
            status: 'failed',
            input: {$input},
            expected: {$expected},
            got: {$firstParamName}
        });
    }
} catch (error) {
    failed++;
    results.push({
        status: 'error',
        input: {$input},
        expected: {$expected},
        error: error.message
    });
}
JS;
                } else {
                    $cases[] = <<<JS
try {
    const result = {$functionName}({$inputArgs});
    const isEqual = deepEqual(result, {$expected});
    if (isEqual) {
        passed++;
        results.push({
            status: 'passed',
            input: {$input},
            expected: {$expected},
            got: result
        });
    } else {
        failed++;
        results.push({
            status: 'failed',
            input: {$input},
            expected: {$expected},
            got: result
        });
    }
} catch (error) {
    failed++;
    results.push({
        status: 'error',
        input: {$input},
        expected: {$expected},
        error: error.message
    });
}
JS;
                }
            } elseif ($lang === 'python') {
                if ($isVoidReturn) {
                    // For in-place functions, compare the modified input
                    $cases[] = <<<PY
try:
    import copy
    {$firstParamName} = copy.deepcopy({$input}[0])
    {$functionName}({$firstParamName})
    is_equal = deep_equal({$firstParamName}, {$expected})
    if is_equal:
        passed += 1
        results.append({
            'status': 'passed',
            'input': {$input},
            'expected': {$expected},
            'got': {$firstParamName}
        })
    else:
        failed += 1
        results.append({
            'status': 'failed',
            'input': {$input},
            'expected': {$expected},
            'got': {$firstParamName}
        })
except Exception as e:
    failed += 1
    results.append({
        'status': 'error',
        'input': {$input},
        'expected': {$expected},
        'error': str(e)
    })
PY;
                } else {
                    $cases[] = <<<PY
try:
    result = {$functionName}({$inputArgs})
    is_equal = deep_equal(result, {$expected})
    if is_equal:
        passed += 1
        results.append({
            'status': 'passed',
            'input': {$input},
            'expected': {$expected},
            'got': result
        })
    else:
        failed += 1
        results.append({
            'status': 'failed',
            'input': {$input},
            'expected': {$expected},
            'got': result
        })
except Exception as e:
    failed += 1
    results.append({
        'status': 'error',
        'input': {$input},
        'expected': {$expected},
        'error': str(e)
    })
PY;
                }
            } else { // php
                if ($isVoidReturn) {
                    // For in-place functions, compare the modified input
                    $cases[] = <<<PHP
try {
    \${$firstParamName} = json_decode(json_encode({$input}[0]), true);
    {$functionName}(\${$firstParamName});
    \$is_equal = deep_equal(\${$firstParamName}, {$expected});
    if (\$is_equal) {
        \$passed++;
        \$results[] = [
            'status' => 'passed',
            'input' => {$input},
            'expected' => {$expected},
            'got' => \${$firstParamName}
        ];
    } else {
        \$failed++;
        \$results[] = [
            'status' => 'failed',
            'input' => {$input},
            'expected' => {$expected},
            'got' => \${$firstParamName}
        ];
    }
} catch (Exception \$e) {
    \$failed++;
    \$results[] = [
        'status' => 'error',
        'input' => {$input},
        'expected' => {$expected},
        'error' => \$e->getMessage()
    ];
}
PHP;
                } else {
                    $cases[] = <<<PHP
try {
    \$result = {$functionName}({$inputArgs});
    \$is_equal = deep_equal(\$result, {$expected});
    if (\$is_equal) {
        \$passed++;
        \$results[] = [
            'status' => 'passed',
            'input' => {$input},
            'expected' => {$expected},
            'got' => \$result
        ];
    } else {
        \$failed++;
        \$results[] = [
            'status' => 'failed',
            'input' => {$input},
            'expected' => {$expected},
            'got' => \$result
        ];
    }
} catch (Exception \$e) {
    \$failed++;
    \$results[] = [
        'status' => 'error',
        'input' => {$input},
        'expected' => {$expected},
        'error' => \$e->getMessage()
    ];
}
PHP;
                }
            }
        }

        return implode("\n\n", $cases);
    }

    private function formatValue($value, string $lang): string
    {
        $json = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($lang === 'python') {
            // Convert JSON to Python syntax
            $json = str_replace(['true', 'false', 'null'], ['True', 'False', 'None'], $json);
        } elseif ($lang === 'php') {
            // PHP arrays
            if (is_array($value)) {
                return var_export($value, true);
            }
        }

        return $json;
    }

    private function formatInputArgs($input, ProblemSignature $signature, string $lang): string
    {
        if (is_array($input)) {
            if (array_is_list($input)) {
                // List/array arguments - use as-is
                $args = array_map(fn ($v) => $this->formatValue($v, $lang), $input);

                return implode(', ', $args);
            } else {
                // Object/dict - map to signature params
                $params = $signature->params;
                $args = [];
                foreach ($params as $param) {
                    $paramName = $param['name'] ?? null;
                    if ($paramName && isset($input[$paramName])) {
                        $args[] = $this->formatValue($input[$paramName], $lang);
                    } elseif (isset($input[$paramName])) {
                        // Try positional if name doesn't match
                        $args[] = $this->formatValue($input[$paramName], $lang);
                    }
                }
                // Fallback: use all values
                if (empty($args)) {
                    $args = array_map(fn ($v) => $this->formatValue($v, $lang), array_values($input));
                }

                return implode(', ', $args);
            }
        }

        return $this->formatValue($input, $lang);
    }

    private function mapLanguageToPiston(Lang $lang): string
    {
        return match ($lang) {
            Lang::Javascript => 'js',
            Lang::Python => 'python',
            Lang::Php => 'php',
        };
    }

    private function getMainFileName(Lang $lang): string
    {
        return match ($lang) {
            Lang::Javascript => 'main.js',
            Lang::Python => 'main.py',
            Lang::Php => 'main.php',
        };
    }

    private function parseRunnerOutput(array $pistonData, $tests): array
    {
        $stdout = $pistonData['run']['stdout'] ?? '';
        $stderr = $pistonData['run']['stderr'] ?? '';
        $runStatus = $pistonData['run']['status'] ?? null;

        // Check for compilation errors
        if (isset($pistonData['compile']) && $pistonData['compile']['code'] !== 0) {
            return [
                'summary' => [
                    'passed' => 0,
                    'failed' => $tests->count(),
                    'total' => $tests->count(),
                ],
                'cases' => [],
                'compile_error' => $pistonData['compile']['stderr'] ?? 'Compilation failed',
            ];
        }

        // Check for runtime errors
        if ($runStatus !== null && $runStatus !== '') {
            $errorMessage = $stderr;
            if (empty($errorMessage)) {
                $errorMessage = match ($runStatus) {
                    'TO' => 'Runtime error: Execution timeout',
                    'RE' => 'Runtime error: Execution failed',
                    'SG' => 'Runtime error: Process terminated by signal',
                    'OL' => 'Runtime error: Output length exceeded',
                    'EL' => 'Runtime error: Error output length exceeded',
                    default => 'Runtime error: '.$runStatus,
                };
            }

            return [
                'summary' => [
                    'passed' => 0,
                    'failed' => $tests->count(),
                    'total' => $tests->count(),
                ],
                'cases' => [],
                'runtime_error' => $errorMessage,
            ];
        }

        // Try to parse JSON from stdout
        $jsonStart = strpos($stdout, '{');
        if ($jsonStart !== false) {
            $jsonStr = substr($stdout, $jsonStart);
            $jsonEnd = strrpos($jsonStr, '}');
            if ($jsonEnd !== false) {
                $jsonStr = substr($jsonStr, 0, $jsonEnd + 1);
                $parsed = json_decode($jsonStr, true);
                if ($parsed && isset($parsed['summary']) && isset($parsed['cases'])) {
                    return $parsed;
                }
            }
        }

        // Fallback: no valid output
        return [
            'summary' => [
                'passed' => 0,
                'failed' => $tests->count(),
                'total' => $tests->count(),
            ],
            'cases' => [],
            'error' => 'Failed to parse test results. Output: '.substr($stdout, 0, 200),
        ];
    }

    private function checkCompiled(array $pistonData, Lang $lang): bool
    {
        // Languages that require compilation
        if ($lang === Lang::Php) {
            // PHP doesn't require compilation in Piston
            return true;
        }

        // Check if compile stage exists and succeeded
        if (isset($pistonData['compile'])) {
            return $pistonData['compile']['code'] === 0;
        }

        // If no compile stage, assume it's an interpreted language
        return true;
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
