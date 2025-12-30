<?php

namespace App\Services;

use App\Traits\ApiClient;

class PistonClient
{
    use ApiClient;

    protected string $baseUrl;

    protected array $headers = [];

    public function __construct()
    {
        $this->baseUrl = config('services.piston.url', 'http://piston_api:2000');
    }

    /**
     * Execute code via Piston API
     *
     * @param  string  $language  Language identifier (e.g., 'javascript', 'python', 'php')
     * @param  string  $version  Language version (e.g., '18.17.0', '3.10.0', '8.2.0') or '*' for latest
     * @param  array  $files  Array of file objects with 'name' and 'content'
     * @param  string  $stdin  Standard input for the program
     * @param  array  $args  Command line arguments
     * @param  int|null  $compileTimeout  Compile timeout in milliseconds (default: 10000)
     * @param  int|null  $runTimeout  Run timeout in milliseconds (default: 3000)
     * @param  int|null  $compileCpuTime  Compile CPU time limit in milliseconds (default: 10000)
     * @param  int|null  $runCpuTime  Run CPU time limit in milliseconds (default: 3000)
     * @param  int|null  $compileMemoryLimit  Compile memory limit in bytes (-1 for no limit, default: -1)
     * @param  int|null  $runMemoryLimit  Run memory limit in bytes (-1 for no limit, default: -1)
     * @return array Normalized response with 'success', 'data', and error handling
     */
    public function execute(
        string $language,
        string $version,
        array $files,
        string $stdin = '',
        array $args = [],
        ?int $compileTimeout = null,
        ?int $runTimeout = null,
        ?int $compileCpuTime = null,
        ?int $runCpuTime = null,
        ?int $compileMemoryLimit = null,
        ?int $runMemoryLimit = null
    ): array {
        $payload = [
            'language' => $language,
            'version' => $version,
            'files' => $files,
            'stdin' => $stdin,
            'args' => $args,
        ];

        if ($compileTimeout !== null) {
            $payload['compile_timeout'] = $compileTimeout;
        }
        if ($runTimeout !== null) {
            $payload['run_timeout'] = $runTimeout;
        }
        if ($compileCpuTime !== null) {
            $payload['compile_cpu_time'] = $compileCpuTime;
        }
        if ($runCpuTime !== null) {
            $payload['run_cpu_time'] = $runCpuTime;
        }
        if ($compileMemoryLimit !== null) {
            $payload['compile_memory_limit'] = $compileMemoryLimit;
        }
        if ($runMemoryLimit !== null) {
            $payload['run_memory_limit'] = $runMemoryLimit;
        }

        return $this->post('/api/v2/execute', $payload);
    }

    /**
     * Get available runtimes from Piston
     */
    public function getRuntimes(): array
    {
        return $this->get('/api/v2/runtimes');
    }
}
