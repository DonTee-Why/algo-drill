<?php

namespace App\Domains\StateMachine\StageHandlers;

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
    public const PASS_THRESHOLD = 7;

    public const MIN_AUTO_EVALUATOR_SCORE = 4;

    public function __construct(
        private AutoEvaluator $autoEvaluator,
        private CoachEvaluator $coachEvaluator,
    ) {}

    /**
     * Evaluate the clarify stage
     */
    public function evaluate(CoachingSession $session, array $payload): StageResult
    {
        try {
            $autoEvaluatorScores = $this->autoEvaluator->evaluate(Stage::Clarify, $payload, $session);
            $autoEvaluatorTotal = (int) array_sum(array_column(
                $autoEvaluatorScores,
                'score'
            ));

            if ($autoEvaluatorTotal < self::MIN_AUTO_EVALUATOR_SCORE) {
                return new StageResult(
                    stage: Stage::Clarify,
                    evaluator: 'auto',
                    rubricScores: $autoEvaluatorScores,
                    totalScore: '0',
                    passThreshold: (string) self::PASS_THRESHOLD,
                    passed: false,
                    nextState: Stage::Clarify,
                    testResults: [],
                    coachMsg: 'Your clarifications are not detailed enough. Please provide more detail.',
                );
            }

            $coachEvaluatorScores = $this->coachEvaluator->evaluate(Stage::Clarify, $payload, $session);
            $coachEvaluatorTotal = array_sum(array_column(
                $coachEvaluatorScores,
                'score'
            ));

            // Calculate total score (max 12, pass threshold >= 7)
            $totalScore = $autoEvaluatorTotal + $coachEvaluatorTotal;
            $passed = $totalScore >= self::PASS_THRESHOLD;

            $testResults = [];
            $coachMsg = $passed ? null : 'Please provide more detail in your clarifications.';

            return new StageResult(
                stage: Stage::Clarify,
                evaluator: 'auto',
                rubricScores: [
                    ...$autoEvaluatorScores,
                    ...$coachEvaluatorScores,
                ],
                totalScore: (string) $totalScore,
                passThreshold: (string) self::PASS_THRESHOLD,
                passed: $passed,
                nextState: $passed ? Stage::Clarify->next() : Stage::Clarify,
                testResults: $testResults,
                coachMsg: $coachMsg,
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
                totalScore: '0',
                passThreshold: (string) self::PASS_THRESHOLD,
                passed: false,
                nextState: Stage::Clarify,
                testResults: [],
                coachMsg: 'An unexpected error occurred. Please try again.',
            );
        }
    }
}
