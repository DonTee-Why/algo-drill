<?php

declare(strict_types=1);

namespace App\Domains\Coach\Builders\StageCritiqueRequests;

use App\Domains\Coach\Builders\CritiqueRequestContextFactory;
use App\Domains\Coach\Contracts\StageCritiqueRequestBuilder;
use App\Domains\Coach\DTOs\CoachCritiqueRequest;
use App\Domains\Coach\Rubrics\PseudocodeRubric;
use App\Enums\Stage;
use App\Models\CoachingSession;

final class PseudocodeCritiqueRequestBuilder implements StageCritiqueRequestBuilder
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
            stage: Stage::Pseudocode,
            rubric: PseudocodeRubric::items(),
            problemContext: $this->contextFactory->buildProblemContext($session),
            submission: [
                'steps_text' => (string) ($payload['steps_text'] ?? $payload['stepsText'] ?? ''),
            ],
            autoSignals: [],
            coachConstraints: $this->contextFactory->defaultCoachConstraints(),
        );
    }
}
