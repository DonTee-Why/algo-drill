<?php

namespace App\Runner;

use App\Models\ProblemSignature;
use Illuminate\Support\Collection;

/**
 * JavaScript code runner implementation for executing user solutions with generated test harnesses.
 */
class JavascriptRunner implements CodeRunnerInterface
{
    /**
     * @var string The filename for the entry point JavaScript file.
     */
    private string $mainFileName = 'main.js';

    /**
     * Generates the runner code that will execute the user's solution and test cases.
     *
     * @param  ProblemSignature  $signature  The problem signature metadata.
     * @param  string  $userCode  The user's solution code.
     * @param  array|Collection  $tests  Test cases to run against.
     * @return string Generated JavaScript code as a string.
     */
    public function generateRunnerCode(ProblemSignature $signature, string $userCode, array|Collection $tests): string
    {
        $testCases = $this->generateTestCases($signature, $tests);

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

    /**
     * Generates the JavaScript code for test cases, wiring them up according to the function signature.
     *
     * @param  ProblemSignature  $signature  The metadata for the user's function.
     * @param  array|Collection  $tests  The array or collection of test cases.
     * @return string All test case JavaScript code as a string.
     */
    public function generateTestCases(ProblemSignature $signature, array|Collection $tests): string
    {
        $functionName = $signature->function_name;
        $cases = [];

        $isVoidReturn = isset($signature->returns['type']) && $signature->returns['type'] === 'void';

        foreach ($tests as $index => $test) {
            $input = $this->formatValue($test->input);
            $expected = $this->formatValue($test->expected);
            $inputArgs = $this->formatInputArgs($test->input, $signature);

            $firstParamName = $signature->params[0]['name'] ?? 'arg0';

            if ($isVoidReturn) {
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
        }

        return implode("\n\n", $cases);
    }

    /**
     * Returns the main file name for runner scripts.
     *
     * @return string The entry point JavaScript file name.
     */
    public function getMainFileName(): string
    {
        return $this->mainFileName;
    }

    public function getPistonLanguage(): string
    {
        return 'js';
    }

    /**
     * Formats a value into JavaScript argument syntax for test case code.
     *
     * @param  mixed  $value  The value to format.
     * @return string The JavaScript representation of the value.
     */
    private function formatValue(mixed $value): string
    {
        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Constructs the input arguments for a test case based on the problem signature.
     *
     * @param  mixed  $input  The raw input.
     * @param  ProblemSignature  $signature  The function signature for mapping arguments.
     * @return string The input arguments as a comma-separated string.
     */
    private function formatInputArgs(mixed $input, ProblemSignature $signature): string
    {
        if (\is_array($input)) {
            if (array_is_list($input)) {
                $args = array_map(fn ($v) => $this->formatValue($v), $input);

                return implode(', ', $args);
            } else {
                $params = $signature->params;
                $args = [];
                foreach ($params as $param) {
                    $paramName = $param['name'] ?? null;
                    if ($paramName && isset($input[$paramName])) {
                        $args[] = $this->formatValue($input[$paramName]);
                    } elseif (isset($input[$paramName])) {
                        $args[] = $this->formatValue($input[$paramName]);
                    }
                }
                if (empty($args)) {
                    $args = array_map(fn ($v) => $this->formatValue($v), array_values($input));
                }

                return implode(', ', $args);
            }
        }

        return $this->formatValue($input);
    }
}
