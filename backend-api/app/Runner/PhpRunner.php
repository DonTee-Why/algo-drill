<?php

namespace App\Runner;

use App\Models\ProblemSignature;
use Illuminate\Support\Collection;

/**
 * PHP code runner implementation for executing user solutions with generated test harnesses.
 */
class PhpRunner implements CodeRunnerInterface
{
    /**
     * @var string The filename for the entry point PHP file.
     */
    private string $mainFileName = 'main.php';

    /**
     * Generates the runner code that will execute the user's solution and test cases.
     *
     * @param  ProblemSignature  $signature  The problem signature metadata.
     * @param  string  $userCode  The user's solution code.
     * @param  array|Collection  $tests  Test cases to run against.
     * @return string Generated PHP code as a string.
     */
    public function generateRunnerCode(ProblemSignature $signature, string $userCode, array|Collection $tests): string
    {
        $testCases = $this->generateTestCases($signature, $tests);

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

    /**
     * Generates the PHP code for test cases, wiring them up according to the function signature.
     *
     * @param  ProblemSignature  $signature  The metadata for the user's function.
     * @param  array|Collection  $tests  The array or collection of test cases.
     * @return string All test case PHP code as a string.
     */
    public function generateTestCases(ProblemSignature $signature, array|Collection $tests): string
    {
        $functionName = $signature->function_name;
        $cases = [];

        // Check if this is an in-place modification function (void return type)
        $isVoidReturn = isset($signature->returns['type']) && $signature->returns['type'] === 'void';

        foreach ($tests as $index => $test) {
            $input = $this->formatValue($test->input);
            $expected = $this->formatValue($test->expected);
            $inputArgs = $this->formatInputArgs($test->input, $signature);

            // Get the first param name for in-place modifications
            $firstParamName = $signature->params[0]['name'] ?? 'arg0';

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

        return implode("\n\n", $cases);
    }

    /**
     * Returns the main file name for runner scripts.
     *
     * @return string The entry point PHP file name.
     */
    public function getMainFileName(): string
    {
        return $this->mainFileName;
    }

    public function getPistonLanguage(): string
    {
        return 'php';
    }

    /**
     * Formats a value into PHP argument syntax for test case code.
     *
     * @param  mixed  $value  The value to format.
     * @return string The PHP representation of the value.
     */
    private function formatValue(mixed $value): string
    {
        if (\is_array($value)) {
            return var_export($value, true);
        }

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
                // List/array arguments - use as-is
                $args = array_map(fn ($v) => $this->formatValue($v), $input);

                return implode(', ', $args);
            } else {
                // Object/dict - map to signature params
                $params = $signature->params;
                $args = [];
                foreach ($params as $param) {
                    $paramName = $param['name'] ?? null;
                    if ($paramName && isset($input[$paramName])) {
                        $args[] = $this->formatValue($input[$paramName]);
                    } elseif (isset($input[$paramName])) {
                        // Try positional if name doesn't match
                        $args[] = $this->formatValue($input[$paramName]);
                    }
                }
                // Fallback: use all values
                if (empty($args)) {
                    $args = array_map(fn ($v) => $this->formatValue($v), array_values($input));
                }

                return implode(', ', $args);
            }
        }

        return $this->formatValue($input);
    }
}
