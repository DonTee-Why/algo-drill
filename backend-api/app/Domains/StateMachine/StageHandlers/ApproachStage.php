<?php

declare(strict_types=1);

namespace App\Domains\StateMachine\StageHandlers;

use App\Domains\Coach\Rubrics\ApproachRubric;
use App\Domains\Evaluator\CoachEvaluator;
use App\Domains\StateMachine\Contracts\StageHandler;
use App\Domains\StateMachine\DTOs\StageResult;
use App\Enums\Stage;
use App\Models\CoachingSession;
use Exception;
use Illuminate\Support\Facades\Log;

class ApproachStage implements StageHandler
{
    public function __construct(
        private CoachEvaluator $coachEvaluator,
    ) {}

    /**
     * Evaluate the approach stage
     */
    public function evaluate(CoachingSession $session, array $coachingSessionPayload): StageResult
    {
        try {
            $critique = $this->coachEvaluator->evaluate(
                Stage::Approach,
                $coachingSessionPayload,
                $session,
            );

            if ($critique->available) {
                $rubricScores = $critique->scores;
                $totalScore = (int) array_sum(array_column($rubricScores, 'score'));
                $coachMsg = $critique->coachMsg;
                $flags = $critique->flags;
                $questions = $critique->questions;
                $evaluator = 'coach';
            } else {
                $rubricScores = [];
                $totalScore = 0;
                $coachMsg = $critique->coachMsg;
                $flags = [];
                $questions = [];
                $evaluator = 'auto';
            }

            $passed = $totalScore >= ApproachRubric::PASS_THRESHOLD;

            if ($passed) {
                $coachMsg = $coachMsg ?? null;
            } else {
                $coachMsg = $coachMsg ?? 'Please provide more detail in your approach.';
            }

            return new StageResult(
                stage: Stage::Approach,
                evaluator: $evaluator,
                rubricScores: $rubricScores,
                totalScore: $totalScore,
                passThreshold: ApproachRubric::PASS_THRESHOLD,
                passed: $passed,
                nextState: $passed ? Stage::Approach->next() : Stage::Approach,
                testResults: [],
                coachMsg: $coachMsg,
                flags: $flags,
                questions: $questions,
            );
        } catch (Exception $e) {
            Log::error(
                'Error evaluating approach stage: '.$e->getMessage(),
                [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTrace(),
                ]
            );

            return new StageResult(
                stage: Stage::Approach,
                evaluator: 'auto',
                rubricScores: [],
                totalScore: 0,
                passThreshold: ApproachRubric::PASS_THRESHOLD,
                passed: false,
                nextState: Stage::Approach,
                testResults: [],
                coachMsg: 'An unexpected error occurred. Please try again.',
            );
        }
    }
}
