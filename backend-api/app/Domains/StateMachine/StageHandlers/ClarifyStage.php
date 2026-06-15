<?php

declare(strict_types=1);

namespace App\Domains\StateMachine\StageHandlers;

use App\Domains\Coach\Rubrics\ClarifyRubric;
use App\Domains\Evaluator\AutoEvaluator;
use App\Domains\Evaluator\CoachEvaluator;
use App\Domains\StateMachine\Contracts\StageHandler;
use App\Domains\StateMachine\DTOs\StageResult;
use App\Enums\Stage;
use App\Models\CoachingSession;
use Exception;
use Illuminate\Support\Facades\Log;

class ClarifyStage implements StageHandler
{
    public function __construct(
        private AutoEvaluator $autoEvaluator,
        private CoachEvaluator $coachEvaluator,
    ) {}

    /**
     * Evaluate the clarify stage
     */
    public function evaluate(CoachingSession $session, array $coachingSessionPayload): StageResult
    {
        try {
            $autoEvaluatorScores = $this->autoEvaluator->evaluate(Stage::Clarify, $coachingSessionPayload, $session);
            $autoEvaluatorTotal = (int) array_sum(array_column(
                $autoEvaluatorScores,
                'score'
            ));

            if ($autoEvaluatorTotal < ClarifyRubric::MIN_AUTO_EVALUATOR_SCORE) {
                return new StageResult(
                    stage: Stage::Clarify,
                    evaluator: 'auto',
                    rubricScores: $autoEvaluatorScores,
                    totalScore: $autoEvaluatorTotal,
                    passThreshold: ClarifyRubric::PASS_THRESHOLD,
                    passed: false,
                    nextState: Stage::Clarify,
                    testResults: [],
                    coachMsg: 'Your clarifications are not detailed enough. Please provide more detail.',
                );
            }

            $critique = $this->coachEvaluator->evaluate(Stage::Clarify, $coachingSessionPayload, $session);

            if ($critique->available) {
                $rubricScores = $critique->scores;
                $totalScore = (int) array_sum(array_column($rubricScores, 'score'));
                $coachMsg = $critique->coachMsg;
                $flags = $critique->flags;
                $questions = $critique->questions;
                $evaluator = 'coach';
            } else {
                $rubricScores = $autoEvaluatorScores;
                $totalScore = $autoEvaluatorTotal;
                $coachMsg = $critique->coachMsg;
                $flags = [];
                $questions = [];
                $evaluator = 'auto';
            }

            $passed = $totalScore >= ClarifyRubric::PASS_THRESHOLD;

            if ($passed) {
                $coachMsg = $coachMsg ?? null;
            } else {
                $coachMsg = $coachMsg ?? 'Please provide more detail in your clarifications.';
            }

            return new StageResult(
                stage: Stage::Clarify,
                evaluator: $evaluator,
                rubricScores: $rubricScores,
                totalScore: $totalScore,
                passThreshold: ClarifyRubric::PASS_THRESHOLD,
                passed: $passed,
                nextState: $passed ? Stage::Clarify->next() : Stage::Clarify,
                testResults: [],
                coachMsg: $coachMsg,
                flags: $flags,
                questions: $questions,
            );
        } catch (Exception $e) {
            Log::error(
                'Error evaluating clarify stage: '.$e->getMessage(),
                [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTrace(),
                ]
            );

            return new StageResult(
                stage: Stage::Clarify,
                evaluator: 'auto',
                rubricScores: [],
                totalScore: 0,
                passThreshold: ClarifyRubric::PASS_THRESHOLD,
                passed: false,
                nextState: Stage::Clarify,
                testResults: [],
                coachMsg: 'An unexpected error occurred. Please try again.',
            );
        }
    }
}
