<?php

declare(strict_types=1);

namespace App\Domains\Coach\Builders\StageCritiqueRequests;

use App\Domains\Coach\Builders\CritiqueRequestContextFactory;
use App\Domains\Coach\Contracts\StageCritiqueRequestBuilder;
use App\Domains\Coach\DTOs\CoachCritiqueRequest;
use App\Domains\Coach\Rubrics\BruteForceRubric;
use App\Enums\Stage;
use App\Models\CoachingSession;

final class BruteForceCritiqueRequestBuilder implements StageCritiqueRequestBuilder
{
    public function __construct(
        private CritiqueRequestContextFactory $contextFactory,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function build(CoachingSession $session, array $payload): CoachCritiqueRequest
    {
        return new CoachCritiqueRequest(
            sessionId: $session->id,
            stage: Stage::BruteForce,
            rubric: BruteForceRubric::items(),
            problemContext: $this->contextFactory->buildProblemContext($session),
            submission: [
                'code' => (string) ($payload['code'] ?? ''),
                'lang' => (string) ($payload['lang'] ?? $session->selected_lang ?? ''),
            ],
            autoSignals: $this->autoSignals($payload),
            coachConstraints: $this->contextFactory->defaultCoachConstraints(),
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function autoSignals(array $payload): array
    {
        $runner = is_array($payload['runner'] ?? null) ? $payload['runner'] : [];

        if ($runner === []) {
            return [];
        }

        $summary = is_array($runner['tests']['summary'] ?? null) ? $runner['tests']['summary'] : [];

        return [
            'compiled' => (bool) ($runner['compiled'] ?? false),
            'signature_ok' => (bool) ($runner['signature_ok'] ?? false),
            'tests_passed' => (int) ($summary['passed'] ?? 0),
            'tests_failed' => (int) ($summary['failed'] ?? 0),
            'tests_total' => (int) ($summary['total'] ?? 0),
        ];
    }
}
