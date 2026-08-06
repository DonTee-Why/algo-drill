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
            autoSignals: [],
            coachConstraints: $this->contextFactory->defaultCoachConstraints(),
        );
    }
}
