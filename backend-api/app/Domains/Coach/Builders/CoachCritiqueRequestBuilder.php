<?php

declare(strict_types=1);

namespace App\Domains\Coach\Builders;

use App\Domains\Coach\Builders\StageCritiqueRequests\ApproachCritiqueRequestBuilder;
use App\Domains\Coach\Builders\StageCritiqueRequests\BruteForceCritiqueRequestBuilder;
use App\Domains\Coach\Builders\StageCritiqueRequests\ClarifyCritiqueRequestBuilder;
use App\Domains\Coach\Builders\StageCritiqueRequests\OptimizeCritiqueRequestBuilder;
use App\Domains\Coach\Builders\StageCritiqueRequests\PseudocodeCritiqueRequestBuilder;
use App\Domains\Coach\DTOs\CoachCritiqueRequest;
use App\Enums\Stage;
use App\Models\CoachingSession;
use InvalidArgumentException;

final class CoachCritiqueRequestBuilder
{
    public function __construct(
        private ClarifyCritiqueRequestBuilder $clarify,
        private ApproachCritiqueRequestBuilder $approach,
        private PseudocodeCritiqueRequestBuilder $pseudocode,
        private BruteForceCritiqueRequestBuilder $bruteForce,
        private OptimizeCritiqueRequestBuilder $optimize,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function build(CoachingSession $session, Stage $stage, array $payload): CoachCritiqueRequest
    {
        return match ($stage) {
            Stage::Clarify => $this->clarify->build($session, $payload),
            Stage::Approach => $this->approach->build($session, $payload),
            Stage::Pseudocode => $this->pseudocode->build($session, $payload),
            Stage::BruteForce => $this->bruteForce->build($session, $payload),
            Stage::Optimize => $this->optimize->build($session, $payload),
            default => throw new InvalidArgumentException("Coach critique is not supported for stage [{$stage->value}]."),
        };
    }
}
