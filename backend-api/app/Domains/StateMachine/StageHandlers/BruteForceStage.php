<?php

namespace App\Domains\StateMachine\StageHandlers;

use App\Domains\Evaluator\AutoEvaluator;
use App\Domains\StateMachine\Contracts\StageHandler;
use App\Domains\StateMachine\DTOs\StageResult;
use App\Models\CoachingSession;
use App\Enums\Stage;

class BruteForceStage implements StageHandler {
    public function __construct(private AutoEvaluator $autoEvaluator) {}
    /**
     * Evaluate the brute force stage
     *
     * @param CoachingSession $session
     * @param array $payload
     * @return StageResult
     */
    public function evaluate(CoachingSession $session, array $payload): StageResult {
        // Call runnerService(Piston) to evaluate the code
        // $runnerResult = $this->runnerService->run($payload['code'], $payload['lang']);
        // $payload['runner'] = $runner;

        $rubricScores = $this->autoEvaluator->evaluate(Stage::BruteForce, $payload);

        // Call coachEvaluator to evaluate the code for correctness and get the coach message
        // $coachEvaluation = $this->coachEvaluator->evaluate(Stage::BruteForce, $payload, $runnerResult);
        // $rubricScores['correctness'] = [
            // $coachEvaluation['score'], 'by' => 'coach'];
        // ];


        // Calculate total score (max 12, pass threshold >= 7)
        $totalScore = $rubricScores['total'];
        $passed = $totalScore >= 5;

        unset($rubricScores['total']);

        $testResults = [];
        $coachMsg = $passed ? null : 'Please provide more detail in your brute force solution.';

        return new StageResult(
            $rubricScores,
            $passed,
            $passed ? Stage::BruteForce->next() : Stage::BruteForce,
            $testResults,
            $coachMsg
        );
    }
}