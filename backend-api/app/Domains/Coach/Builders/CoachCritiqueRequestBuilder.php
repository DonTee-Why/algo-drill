<?php

declare(strict_types=1);

namespace App\Domains\Coach\Builders;

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
     * @return array<string, mixed>
     */
    public function build(CoachingSession $session, Stage $stage, array $payload): array
    {
        return match ($stage) {
            Stage::Clarify => $this->buildClarify($session, $payload),
            default => throw new InvalidArgumentException("Coach critique is not supported for stage [{$stage->value}]."),
        };
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function buildClarify(CoachingSession $session, array $payload): array
    {
        $session->loadMissing(['problem.signatures']);

        $signature = $this->resolveSignature($session);

        return [
            'session_id' => $session->id,
            'stage' => Stage::Clarify->value,
            'rubric' => ClarifyRubric::items(),
            'problem_context' => $this->buildProblemContext($session, $signature),
            'submission' => [
                'inputs_outputs' => (string) ($payload['inputs_outputs'] ?? ''),
                'constraints' => (string) ($payload['constraints'] ?? ''),
                'examples' => $payload['examples'] ?? '',
            ],
            'auto_signals' => $this->autoSignalsBuilder->build($payload, $signature),
            'coach_constraints' => [
                'no_code' => true,
                'no_solution_reveal' => true,
                'feedback_style' => 'socratic',
                'max_questions' => 2,
                'max_tokens' => 500,
            ],
        ];
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

    /**
     * @return array<string, mixed>
     */
    private function buildProblemContext(CoachingSession $session, ?ProblemSignature $signature): array
    {
        $problem = $session->problem;

        return [
            'title' => $problem->title,
            'description' => $problem->description_md,
            'tags' => $problem->tags ?? [],
            'problem_constraints' => $problem->constraints ?? [],
            'signature' => $signature !== null ? [
                'function_name' => $signature->function_name,
                'params' => $signature->params,
                'returns' => $signature->returns,
            ] : null,
        ];
    }
}
