<?php

declare(strict_types=1);

namespace App\Domains\Coach\Builders;

use App\Domains\Coach\DTOs\CoachConstraints;
use App\Domains\Coach\DTOs\ProblemContext;
use App\Domains\Coach\DTOs\ProblemSignatureContext;
use App\Enums\Lang;
use App\Models\CoachingSession;
use App\Models\ProblemSignature;

final class CritiqueRequestContextFactory
{
    public function resolveSignature(CoachingSession $session): ?ProblemSignature
    {
        $session->loadMissing(['problem.signatures']);

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

    public function buildProblemContext(CoachingSession $session, ?ProblemSignature $signature = null): ProblemContext
    {
        $session->loadMissing(['problem.signatures']);

        $problem = $session->problem;
        $signature ??= $this->resolveSignature($session);

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

    public function defaultCoachConstraints(): CoachConstraints
    {
        return new CoachConstraints(
            noCode: true,
            noSolutionReveal: true,
            feedbackStyle: 'socratic',
            maxQuestions: 2,
            maxTokens: 500,
        );
    }
}
