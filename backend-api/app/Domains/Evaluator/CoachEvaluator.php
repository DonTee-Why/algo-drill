<?php

declare(strict_types=1);

namespace App\Domains\Evaluator;

use App\Domains\Coach\Builders\CoachCritiqueRequestBuilder;
use App\Domains\Coach\DTOs\CoachCritiqueResult;
use App\Domains\Evaluator\Contracts\RubricEvaluator;
use App\Enums\Stage;
use App\Models\CoachingSession;
use App\Services\CoachClient;
use Illuminate\Support\Facades\Log;
use Throwable;

class CoachEvaluator implements RubricEvaluator
{
    public function __construct(
        private CoachClient $coachClient,
        private CoachCritiqueRequestBuilder $requestBuilder,
    ) {}

    /**
     * Request a qualitative critique from the coach sidecar for the given stage.
     *
     * The per-stage request shape is owned by the request builder; this method is
     * stage-agnostic and simply builds, calls, and maps the response.
     *
     * @param  array<string, mixed>  $payload
     */
    public function evaluate(Stage $stage, array $payload, CoachingSession $session): CoachCritiqueResult
    {
        try {
            $requestPayload = $this->requestBuilder->build($session, $stage, $payload);
            $response = $this->coachClient->critique($requestPayload->toArray());

            if (! ($response['success'] ?? false)) {
                Log::warning('Coach critique request failed', [
                    'session_id' => $session->id,
                    'stage' => $stage->value,
                    'message' => $response['message'] ?? 'Unknown coach error',
                ]);

                return $this->unavailableResult();
            }

            $data = \is_array($response['data'] ?? null) ? $response['data'] : [];
            $flags = \is_array($data['flags'] ?? null) ? $data['flags'] : [];

            if ($this->isUnusableCritique($flags)) {
                Log::warning('Coach critique returned an unusable fallback', [
                    'session_id' => $session->id,
                    'stage' => $stage->value,
                    'fallback_used' => (bool) ($flags['fallback_used'] ?? false),
                    'invalid_json' => (bool) ($flags['invalid_json'] ?? false),
                ]);

                return $this->unavailableResult();
            }

            return new CoachCritiqueResult(
                scores: $this->mapScores($data['scores'] ?? []),
                coachMsg: $data['coach_msg'] ?? null,
                flags: $flags,
                questions: \is_array($data['questions'] ?? null) ? array_values($data['questions']) : [],
                available: true,
            );
        } catch (Throwable $e) {
            Log::error('Coach critique exception', [
                'session_id' => $session->id,
                'stage' => $stage->value,
                'error' => $e->getMessage(),
            ]);

            return $this->unavailableResult();
        }
    }

    private function unavailableResult(): CoachCritiqueResult
    {
        return new CoachCritiqueResult(
            scores: [],
            coachMsg: 'Coach is temporarily unavailable. Your submission was scored using automated checks only.',
            flags: [],
            questions: [],
            available: false,
        );
    }

    /**
     * @param  array<string, mixed>  $flags
     */
    private function isUnusableCritique(array $flags): bool
    {
        return (bool) ($flags['fallback_used'] ?? false)
            || (bool) ($flags['invalid_json'] ?? false);
    }

    /**
     * @param  array<string, array<string, mixed>>  $scores
     * @return array<string, array{score: int, max_score: int|null, reason: string|null, by: string}>
     */
    private function mapScores(array $scores): array
    {
        $mapped = [];

        foreach ($scores as $key => $entry) {
            if (! \is_array($entry)) {
                continue;
            }

            $mapped[$key] = [
                'score' => (int) ($entry['score'] ?? 0),
                'max_score' => isset($entry['max_score']) ? (int) $entry['max_score'] : null,
                'reason' => isset($entry['reason']) ? (string) $entry['reason'] : null,
                'by' => 'coach',
            ];
        }

        return $mapped;
    }
}
