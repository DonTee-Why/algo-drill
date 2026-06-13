<?php

namespace App\Domains\StateMachine\StageHandlers;

use App\Domains\Evaluator\AutoEvaluator;
use App\Domains\Evaluator\CoachEvaluator;
use App\Domains\StateMachine\Contracts\StageHandler;
use App\Domains\StateMachine\DTOs\StageResult;
use App\Enums\Stage;
use App\Models\CoachingSession;
use App\Services\TestHarnessService;
use Exception;
use Illuminate\Support\Facades\Log;

class BruteForceStage implements StageHandler
{
    public const PASS_THRESHOLD = 5;

    public function __construct(
        private AutoEvaluator $autoEvaluator,
        private CoachEvaluator $coachEvaluator,
        private TestHarnessService $testHarnessService
    ) {}

    /**
     * Evaluate the brute force stage
     */
    public function evaluate(CoachingSession $session, array $payload): StageResult
    {
        $code = $payload['code'] ?? '';
        $lang = $payload['lang'] ?? null;

        try {
            $runnerResult = $this->testHarnessService->runCode($session, $lang, $code, isSubmission: true);

            $rubricPayload = $payload;
            $rubricPayload['runner'] = $runnerResult;

            $autoEvaluation = $this->autoEvaluator->evaluate(Stage::BruteForce, $rubricPayload, $session);
            $coachEvaluation = $this->coachEvaluator->evaluate(Stage::BruteForce, $rubricPayload, $session);

            $rubricScores = [
                ...$autoEvaluation,
                ...$coachEvaluation,
            ];
            $totalScore = array_sum(array_column($rubricScores, 'score')) ?? 0;

            $allTestsGreen = ($runnerResult['tests']['summary']['failed'] ?? 1) === 0;
            $passed = $totalScore >= self::PASS_THRESHOLD && $allTestsGreen;

            $testResults = $runnerResult['tests'] ?? [];
            $coachMsg = $coachEvaluation['coach_msg'] ?? ($passed ? null : 'Please provide more detail in your brute force solution.');

            return new StageResult(
                stage: Stage::BruteForce,
                evaluator: 'auto+coach',
                rubricScores: $rubricScores,
                totalScore: (string) $totalScore,
                passThreshold: (string) self::PASS_THRESHOLD,
                passed: $passed,
                nextState: $passed ? Stage::BruteForce->next() : Stage::BruteForce,
                testResults: $testResults,
                coachMsg: $coachMsg,
            );
        } catch (Exception $e) {
            Log::error(
                'Error evaluating brute force stage: '.$e->getMessage(),
                [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTrace(),
                ]
            );

            return new StageResult(
                stage: Stage::BruteForce,
                evaluator: 'auto+coach',
                rubricScores: [],
                totalScore: '0',
                passThreshold: (string) self::PASS_THRESHOLD,
                passed: false,
                nextState: Stage::BruteForce,
                testResults: [],
                coachMsg: 'An unexpected error occurred. Please try again.',
            );
        }
    }
}
