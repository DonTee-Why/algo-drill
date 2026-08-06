<?php

declare(strict_types=1);

namespace App\Domains\Coach\Builders;

use App\Domains\Coach\DTOs\CoachCritiqueRequest;
use App\Domains\Coach\DTOs\ProblemContext;
use App\Domains\Coach\DTOs\ProblemSignatureContext;
use App\Domains\Coach\Rubrics\ClarifyRubric;
use App\Enums\Lang;
use App\Enums\Stage;
use App\Models\CoachingSession;
use App\Models\ProblemSignature;
use InvalidArgumentException;

final class CoachCritiqueRequestBuilder
{
    public function __construct(
        private ClarifyAutoSignalsBuilder $autoSignalsBuilder,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function build(CoachingSession $session, Stage $stage, array $payload): CoachCritiqueRequest
    {
        return match ($stage) {
            Stage::Clarify => $this->buildClarify($session, $payload),
            default => throw new InvalidArgumentException("Coach critique is not supported for stage [{$stage->value}]."),
        };
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function buildClarify(CoachingSession $session, array $payload): CoachCritiqueRequest
    {
        $session->loadMissing(['problem.signatures']);

        $signature = $this->resolveSignature($session);

        return new CoachCritiqueRequest(
            sessionId: $session->id,
            stage: Stage::Clarify,
            rubric: ClarifyRubric::items(),
            problemContext: $this->buildProblemContext($session, $signature),
            submission: [
                'inputs_outputs' => (string) ($payload['inputs_outputs'] ?? ''),
                'constraints' => (string) ($payload['constraints'] ?? ''),
                'examples' => $payload['examples'] ?? '',
            ],
            autoSignals: $this->autoSignalsBuilder->build($payload, $signature),
            coachConstraints: [
                'no_code' => true,
                'no_solution_reveal' => true,
                'feedback_style' => 'socratic',
                'max_questions' => 2,
                'max_tokens' => 500,
            ],
        );
    }

    private function resolveSignature(CoachingSession $session): ?ProblemSignature
    {
        $signatures = $session->problem->signatures;

        if ($signatures->isEmpty()) {
            return null;
        }

        $selectedLang = Lang::tryFrom($session->selected_lang);

        if ($selectedLang !== null) {
            $match = $signatures->firstWhere('lang', $selectedLang);
            if ($match !== null) {
                return $match;
            }
        }

        return $signatures->first();
    }

    private function buildProblemContext(CoachingSession $session, ?ProblemSignature $signature): ProblemContext
    {
        $problem = $session->problem;

        return new ProblemContext(
            title: $problem->title,
            description: $problem->description_md,
            tags: $problem->tags ?? [],
            problemConstraints: $problem->constraints ?? [],
            signature: $signature !== null ? new ProblemSignatureContext(
                functionName: $signature->function_name,
                params: $signature->params,
                returns: $signature->returns,
            ) : null,
        );
    }
}
