<?php

namespace App\Runner;

use App\Enums\Lang;
use App\Models\ProblemSignature;
use App\Services\PistonClient;
use Illuminate\Support\Collection;

class CodeRunnerEngine
{
    public function __construct(
        private CodeRunnerFactory $codeRunnerFactory,
        private PistonClient $pistonClient
    ) {}

    public function run(Lang $lang, ProblemSignature $signature, string $userCode, array|Collection $tests): array
    {
        $codeRunner = $this->codeRunnerFactory->make($lang);
        $runnerCode = $codeRunner->generateRunnerCode($signature, $userCode, $tests);

        $pistonResponse = $this->pistonClient->execute(
            language: $codeRunner->getPistonLanguage(),
            version: '*',
            files: [
                [
                    'name' => $codeRunner->getMainFileName(),
                    'content' => $runnerCode,
                ],
            ],
            stdin: '',
            args: [],
            runTimeout: 3000,
            runCpuTime: 3000,
            runMemoryLimit: 128 * 1024 * 1024,
        );
        if (! $pistonResponse['success']) {
            throw new \Exception('Failed to execute code runner: '.$pistonResponse['message']);
        }

        $pistonData = $pistonResponse['data'];

        $testResults = $this->parseRunnerOutput($pistonData, $tests);
        $compiled = $this->checkCompiled($pistonData);
        $metrics = $this->extractMetrics($pistonData);

        return [
            'testResults' => $testResults,
            'cpuMs' => $metrics['cpuMs'],
            'memKb' => $metrics['memKb'],
            'stderrTruncated' => $metrics['stderrTruncated'],
            'compiled' => $compiled,
        ];
    }

    private function parseRunnerOutput(array $pistonData, Collection $tests): array
    {
        $stdout = $pistonData['run']['stdout'] ?? '';
        $stderr = $pistonData['run']['stderr'] ?? '';
        $runStatus = $pistonData['run']['status'] ?? null;

        // Check for compilation errors
        if (isset($pistonData['compile']) && $pistonData['compile']['code'] !== 0) {
            return $this->getOutput(
                $tests,
                'compile_error',
                $pistonData['compile']['stderr'] ?? 'Compilation failed'
            );
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
                    default => "Runtime error: $runStatus",
                };
            }

            return $this->getOutput(
                $tests,
                'runtime_error',
                $errorMessage,
            );
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
        return $this->getOutput(
            $tests,
            'error',
            'Failed to parse test results. Output: '.substr($stdout, 0, 200),
        );
    }

    private function extractMetrics(array $pistonResponse): array
    {
        $cpuMs = $pistonResponse['run']['cpu_time'] ?? null;
        $memKb = isset($pistonResponse['run']['memory']) ? (int) ($pistonResponse['run']['memory'] / 1024) : null;

        // Check if stderr was truncated (Piston limits stdout/stderr)
        if (isset($pistonResponse['run']['stderr'])) {
            $stderrLength = strlen($pistonResponse['run']['stderr']);
            // Piston defaults to 1024 char limit for stdout/stderr
            $stderrTruncated = $stderrLength >= 1024;
        }

        return [
            'cpuMs' => $cpuMs,
            'memKb' => $memKb,
            'stderrTruncated' => $stderrTruncated ?? false,
        ];
    }

    private function getOutput(Collection $tests, string $errorType, ?string $errorMessage = null): array
    {
        return [
            'summary' => [
                'passed' => 0,
                'failed' => $tests->count(),
                'total' => $tests->count(),
            ],
            'cases' => [],
            $errorType => $errorMessage,
        ];
    }

    private function checkCompiled(array $pistonData): bool
    {
        if (isset($pistonData['compile'])) {
            return $pistonData['compile']['code'] === 0;
        }
        return true;
    }
}
