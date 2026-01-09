<?php

namespace App\Traits;

use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

trait ApiClient
{
    public function get(string $url, array $params = [], array $headers = [])
    {
        try {
            $response = $this->client($headers)->get($url, $params);

            return $this->handleResponse($response, $url);
        } catch (Exception $e) {
            return $this->handleResponse($e, $url);
        }
    }

    public function post(string $url, array $data = [], array $headers = [])
    {
        try {
            $response = $this->client($headers)->post($url, $data);

            return $this->handleResponse($response, $url);
        } catch (Exception $e) {
            return $this->handleResponse($e, $url);
        }
    }

    protected function client(?array $headers = [])
    {
        $headers = [
            ...$this->headers,
            ...$headers,
        ];

        return Http::baseUrl($this->baseUrl)
            ->withHeaders($headers)
            ->timeout(30)
            ->withUserAgent('AlgoDrill/1.0')
            ->acceptJson()
            ->contentType('application/json');
    }

    /**
     * Handle HTTP response and return standardized format.
     */
    private function handleResponse(Response|Exception $response, string $endpoint): array
    {
        if ($response instanceof Exception) {
            Log::error('API call exception', [
                'endpoint' => $endpoint,
                'error' => $response->getMessage(),
                'trace' => $response->getTrace(),
            ]);

            return [
                'success' => false,
                'message' => 'API connection failed: '.$response->getMessage(),
                'data' => null,
            ];
        }

        if ($response->failed()) {
            Log::error('API call failed', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => $response->json('message')
                    ?? 'API call failed with status: '.$response->status(),
                'status_code' => $response->status(),
                'data' => $response->json(),
            ];
        }

        try {
            $data = $response->json();

            return [
                'success' => true,
                'message' => 'API call successful',
                'data' => $data,
            ];
        } catch (RuntimeException $e) {
            Log::error('API response parsing failed', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
                'response_body' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to parse API response: '.$e->getMessage(),
                'data' => null,
            ];
        }
    }
}
