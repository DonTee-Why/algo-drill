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
    public function __construct(
        private AutoEvaluator $autoEvaluator,
        private CoachEvaluator $coachEvaluator,
        private TestHarnessService $testHarnessService
    ) {}

    /**
     * Evaluate the brute force stage
     *
     * @param CoachingSession $session
     * @param array $payload
     * @return StageResult
     */
    public function evaluate(CoachingSession $session, array $payload): StageResult
    {
        $code = $payload['code'] ?? '';
        $lang = $payload['lang'] ?? null;

        try {
            $runnerResult = $this->testHarnessService->runCode($session, $lang, $code);


            $rubricPayload = $payload;
            $rubricPayload['runner'] = $runnerResult;

            $autoEvaluation = $this->autoEvaluator->evaluate(Stage::BruteForce, $rubricPayload);
            $coachEvaluation = $this->coachEvaluator->evaluate(Stage::BruteForce, $rubricPayload);

            $rubricScores = [
                ...$autoEvaluation,
                ...$coachEvaluation
            ];
            $totalScore = array_sum(array_column($rubricScores, 'score')) ?? 0;

            $allTestsGreen = ($runnerResult['tests']['summary']['failed'] ?? 1) === 0;
            $passed = $totalScore >= 5 && $allTestsGreen;

            $testResults = $runnerResult['tests'] ?? [];
            $coachMsg = $coachEvaluation['coach_msg'] ?? ($passed ? null : 'Please provide more detail in your brute force solution.');

            return new StageResult(
                $rubricScores,
                $passed,
                $passed ? Stage::BruteForce->next() : Stage::BruteForce,
                $testResults,
                $coachMsg
            );
        } catch (Exception $e) {
            Log::error(
                'Error evaluating brute force stage: ' . $e->getMessage(),
                [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTrace()
                ]
            );
            return new StageResult(
                [],
                false,
                Stage::BruteForce,
                [],
                'An unexpected error occurred. Please try again.'
            );
        }
    }
}
