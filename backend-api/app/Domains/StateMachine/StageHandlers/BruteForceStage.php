<?php

declare(strict_types=1);

namespace App\Domains\StateMachine\StageHandlers;

use App\Domains\Coach\Rubrics\BruteForceRubric;
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
     */
    public function evaluate(CoachingSession $session, array $coachingSessionPayload): StageResult
    {
        $code = $coachingSessionPayload['code'] ?? '';
        $lang = $coachingSessionPayload['lang'] ?? null;

        try {
            $runnerResult = $this->testHarnessService->runCode($session, $lang, $code, isSubmission: true);

            $rubricPayload = $coachingSessionPayload;
            $rubricPayload['runner'] = $runnerResult;

            $autoScores = $this->autoEvaluator->evaluate(Stage::BruteForce, $rubricPayload, $session);
            $critique = $this->coachEvaluator->evaluate(Stage::BruteForce, $rubricPayload, $session);

            $allTestsGreen = ($runnerResult['tests']['summary']['failed'] ?? 1) === 0;

            if (! $critique->available) {
                $totalScore = (int) array_sum(array_column($autoScores, 'score'));

                return new StageResult(
                    stage: Stage::BruteForce,
                    evaluator: 'auto',
                    rubricScores: $autoScores,
                    totalScore: $totalScore,
                    passThreshold: BruteForceRubric::PASS_THRESHOLD,
                    passed: false,
                    nextState: Stage::BruteForce,
                    testResults: $runnerResult['tests'] ?? [],
                    coachMsg: $critique->coachMsg,
                    flags: [],
                    questions: [],
                );
            }

            $rubricScores = $autoScores;
            if (isset($critique->scores['correctness'])) {
                $rubricScores['correctness'] = $critique->scores['correctness'];
            }

            $totalScore = (int) array_sum(array_column($rubricScores, 'score'));
            $passed = $totalScore >= BruteForceRubric::PASS_THRESHOLD && $allTestsGreen;
            $coachMsg = $critique->coachMsg;

            if ($passed) {
                $coachMsg = $coachMsg ?? null;
            } else {
                $coachMsg = $coachMsg ?? 'Please provide more detail in your brute force solution.';
            }

            return new StageResult(
                stage: Stage::BruteForce,
                evaluator: 'auto+coach',
                rubricScores: $rubricScores,
                totalScore: $totalScore,
                passThreshold: BruteForceRubric::PASS_THRESHOLD,
                passed: $passed,
                nextState: $passed ? Stage::BruteForce->next() : Stage::BruteForce,
                testResults: $runnerResult['tests'] ?? [],
                coachMsg: $coachMsg,
                flags: $critique->flags,
                questions: $critique->questions,
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
                totalScore: 0,
                passThreshold: BruteForceRubric::PASS_THRESHOLD,
                passed: false,
                nextState: Stage::BruteForce,
                testResults: [],
                coachMsg: 'An unexpected error occurred. Please try again.',
            );
        }
    }
}
