<?php

namespace App\Runner;

use App\Models\ProblemSignature;
use Illuminate\Support\Collection;

/**
 * Python code runner implementation for executing user solutions with generated test harnesses.
 */
class PythonRunner implements CodeRunnerInterface
{
    /**
     * @var string The filename for the entry point Python file.
     */
    private string $mainFileName = 'main.py';

    /**
     * Generates the runner code that will execute the user's solution and test cases.
     *
     * @param  ProblemSignature  $signature  The problem signature metadata.
     * @param  string  $userCode  The user's solution code.
     * @param  array|Collection  $tests  Test cases to run against.
     * @return string Generated Python code as a string.
     */
    public function generateRunnerCode(ProblemSignature $signature, string $userCode, array|Collection $tests): string
    {
        $testCases = $this->generateTestCases($signature, $tests);

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

    /**
     * Generates the Python code for test cases, wiring them up according to the function signature.
     *
     * @param  ProblemSignature  $signature  The metadata for the user's function.
     * @param  array|Collection  $tests  The array or collection of test cases.
     * @return string All test case Python code as a string.
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
        }

        return implode("\n\n", $cases);
    }

    /**
     * Returns the main file name for runner scripts.
     *
     * @return string The entry point Python file name.
     */
    public function getMainFileName(): string
    {
        return $this->mainFileName;
    }

    public function getPistonLanguage(): string
    {
        return 'python';
    }

    /**
     * Formats a value into Python argument syntax for test case code.
     *
     * @param  mixed  $value  The value to format.
     * @return string The Python representation of the value.
     */
    private function formatValue(mixed $value): string
    {
        $json = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return str_replace(['true', 'false', 'null'], ['True', 'False', 'None'], $json);
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
