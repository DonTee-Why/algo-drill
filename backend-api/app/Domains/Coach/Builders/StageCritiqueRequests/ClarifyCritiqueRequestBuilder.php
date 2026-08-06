<?php

declare(strict_types=1);

namespace App\Domains\Coach\Builders\StageCritiqueRequests;

use App\Domains\Coach\Builders\ClarifyAutoSignalsBuilder;
use App\Domains\Coach\Builders\CritiqueRequestContextFactory;
use App\Domains\Coach\Contracts\StageCritiqueRequestBuilder;
use App\Domains\Coach\DTOs\CoachCritiqueRequest;
use App\Domains\Coach\Rubrics\ClarifyRubric;
use App\Enums\Stage;
use App\Models\CoachingSession;

final class ClarifyCritiqueRequestBuilder implements StageCritiqueRequestBuilder
{
    public function __construct(
        private CritiqueRequestContextFactory $contextFactory,
        private ClarifyAutoSignalsBuilder $autoSignalsBuilder,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function build(CoachingSession $session, array $payload): CoachCritiqueRequest
    {
        $signature = $this->contextFactory->resolveSignature($session);

        return new CoachCritiqueRequest(
            sessionId: $session->id,
            stage: Stage::Clarify,
            rubric: ClarifyRubric::items(),
            problemContext: $this->contextFactory->buildProblemContext($session, $signature),
            submission: [
                'inputs_outputs' => (string) ($payload['inputs_outputs'] ?? ''),
                'constraints' => (string) ($payload['constraints'] ?? ''),
                'examples' => $payload['examples'] ?? '',
            ],
            autoSignals: $this->autoSignalsBuilder->build($payload, $signature),
            coachConstraints: $this->contextFactory->defaultCoachConstraints(),
        );
    }
}
