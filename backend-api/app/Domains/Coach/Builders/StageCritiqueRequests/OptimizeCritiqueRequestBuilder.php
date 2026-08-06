<?php

declare(strict_types=1);

namespace App\Domains\Coach\Builders\StageCritiqueRequests;

use App\Domains\Coach\Builders\CritiqueRequestContextFactory;
use App\Domains\Coach\Contracts\StageCritiqueRequestBuilder;
use App\Domains\Coach\DTOs\CoachCritiqueRequest;
use App\Domains\Coach\Rubrics\OptimizeRubric;
use App\Enums\Stage;
use App\Models\CoachingSession;

final class OptimizeCritiqueRequestBuilder implements StageCritiqueRequestBuilder
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
            stage: Stage::Optimize,
            rubric: OptimizeRubric::items(),
            problemContext: $this->contextFactory->buildProblemContext($session),
            submission: [
                'code' => (string) ($payload['code'] ?? ''),
                'lang' => (string) ($payload['lang'] ?? $session->selected_lang ?? ''),
                'complexity_analysis' => (string) ($payload['complexity_analysis'] ?? $payload['complexityAnalysis'] ?? ''),
                'optimization_technique' => (string) ($payload['optimization_technique'] ?? $payload['optimizationTechnique'] ?? ''),
                'tradeoffs' => (string) ($payload['tradeoffs'] ?? ''),
            ],
            autoSignals: [],
            coachConstraints: $this->contextFactory->defaultCoachConstraints(),
        );
    }
}
