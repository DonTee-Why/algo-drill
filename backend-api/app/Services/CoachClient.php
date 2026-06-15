<?php

declare(strict_types=1);

namespace App\Services;

use App\Traits\ApiClient;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class CoachClient
{
    use ApiClient;

    protected string $baseUrl;

    protected array $headers = [];

    public function __construct()
    {
        $this->baseUrl = config('services.coach.url', 'http://coach:8000');
    }

    /**
     * Request a stage critique from the coach sidecar.
     *
     * @param  array<string, mixed>  $payload
     * @return array{success: bool, message: string, data: mixed, status_code?: int}
     */
    public function critique(array $payload): array
    {
        return $this->post('/coach/critique', $payload);
    }

    protected function client(?array $headers = []): PendingRequest
    {
        $headers = [
            ...$this->headers,
            ...$headers,
        ];

        $timeout = (int) config('services.coach.timeout', 60);

        return Http::baseUrl($this->baseUrl)
            ->withHeaders($headers)
            ->timeout($timeout)
            ->withUserAgent('AlgoDrill-Backend-API/1.0')
            ->acceptJson()
            ->contentType('application/json');
    }
}
