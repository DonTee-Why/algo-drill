<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domains\Coach\Rubrics\BruteForceRubric;
use App\Domains\StateMachine\DTOs\StageResult;
use App\Domains\StateMachine\StageHandlers\BruteForceStage;
use App\Enums\Stage;
use App\Models\CoachingSession;
use App\Models\Problem;
use App\Models\User;
use App\Services\TestHarnessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mockery\MockInterface;
use Tests\TestCase;

class BruteForceStageTest extends TestCase
{
    use RefreshDatabase;

    public function test_passing_auto_and_coach_scores_advance_to_optimize(): void
    {
        $result = $this->evaluateBruteForce(
            $this->passingRunner(),
            [
                'coach_msg' => 'Naive nested scan is logically sound.',
                'scores' => [
                    'correctness' => ['score' => 2, 'max_score' => 3, 'reason' => 'Mostly correct'],
                ],
                'flags' => ['missing_edge_handling' => true],
                'questions' => ['What do you return when no pair exists?'],
            ],
        );

        $this->assertSame(Stage::BruteForce, $result->stage);
        $this->assertSame('auto+coach', $result->evaluator);
        $this->assertSame(8, $result->totalScore);
        $this->assertSame(BruteForceRubric::PASS_THRESHOLD, $result->passThreshold);
        $this->assertTrue($result->passed);
        $this->assertSame(Stage::Optimize, $result->nextState);
        $this->assertSame('Naive nested scan is logically sound.', $result->coachMsg);
        $this->assertTrue($result->flags['missing_edge_handling']);
        $this->assertSame(['What do you return when no pair exists?'], $result->questions);
        $this->assertSame(3, $result->rubricScores['compiles']['score']);
        $this->assertSame('auto', $result->rubricScores['compiles']['by']);
        $this->assertSame(3, $result->rubricScores['signature']['score']);
        $this->assertSame('auto', $result->rubricScores['signature']['by']);
        $this->assertSame(2, $result->rubricScores['correctness']['score']);
        $this->assertSame('coach', $result->rubricScores['correctness']['by']);
        $this->assertArrayNotHasKey('total', $result->rubricScores);
        $this->assertSame(3, $result->testResults['summary']['passed']);
    }

    public function test_failed_tests_block_advance_even_when_coach_scores_correctness(): void
    {
        $result = $this->evaluateBruteForce(
            $this->failingTestsRunner(),
            [
                'coach_msg' => 'The nested loops look right, but a case is failing.',
                'scores' => [
                    'correctness' => ['score' => 3, 'max_score' => 3, 'reason' => 'Looks correct'],
                ],
                'flags' => [],
                'questions' => [],
            ],
        );

        $this->assertFalse($result->passed);
        $this->assertSame(9, $result->totalScore);
        $this->assertSame(Stage::BruteForce, $result->nextState);
        $this->assertSame('The nested loops look right, but a case is failing.', $result->coachMsg);
    }

    public function test_coach_compiles_and_signature_scores_do_not_overwrite_auto(): void
    {
        $result = $this->evaluateBruteForce(
            $this->passingRunner(),
            [
                'coach_msg' => 'Keep the naive plan.',
                'scores' => [
                    'compiles' => ['score' => 0, 'max_score' => 3, 'reason' => 'Unsure'],
                    'signature' => ['score' => 0, 'max_score' => 3, 'reason' => 'Unsure'],
                    'correctness' => ['score' => 3, 'max_score' => 3, 'reason' => 'Correct'],
                ],
                'flags' => [],
                'questions' => [],
            ],
        );

        $this->assertSame(3, $result->rubricScores['compiles']['score']);
        $this->assertSame('auto', $result->rubricScores['compiles']['by']);
        $this->assertSame(3, $result->rubricScores['signature']['score']);
        $this->assertSame('auto', $result->rubricScores['signature']['by']);
        $this->assertSame(3, $result->rubricScores['correctness']['score']);
        $this->assertSame('coach', $result->rubricScores['correctness']['by']);
        $this->assertTrue($result->passed);
    }

    public function test_unavailable_coach_keeps_auto_scores_and_does_not_advance(): void
    {
        $result = $this->evaluateBruteForce(
            $this->passingRunner(),
            [
                'coach_msg' => 'I could not reliably evaluate this response.',
                'scores' => [
                    'correctness' => ['score' => 0, 'max_score' => 3, 'reason' => 'Unavailable due to invalid model response'],
                ],
                'flags' => [
                    'fallback_used' => true,
                    'invalid_json' => true,
                ],
                'questions' => [],
            ],
        );

        $this->assertFalse($result->passed);
        $this->assertSame('auto', $result->evaluator);
        $this->assertSame(6, $result->totalScore);
        $this->assertSame(Stage::BruteForce, $result->nextState);
        $this->assertSame(3, $result->rubricScores['compiles']['score']);
        $this->assertSame('auto', $result->rubricScores['compiles']['by']);
        $this->assertSame(3, $result->rubricScores['signature']['score']);
        $this->assertSame('auto', $result->rubricScores['signature']['by']);
        $this->assertArrayNotHasKey('correctness', $result->rubricScores);
        $this->assertSame([], $result->flags);
        $this->assertSame([], $result->questions);
        $this->assertStringContainsString('temporarily unavailable', (string) $result->coachMsg);
    }

    public function test_forwards_code_lang_and_runner_signals_to_coach(): void
    {
        config(['services.coach.url' => 'http://coach.test']);

        Http::fake([
            'http://coach.test/coach/critique' => Http::response([
                'coach_msg' => 'Naive nested scan is logically sound.',
                'scores' => [
                    'correctness' => ['score' => 3, 'max_score' => 3, 'reason' => 'Correct'],
                ],
                'flags' => [],
                'questions' => [],
            ]),
        ]);

        $runner = $this->passingRunner();
        $this->mockHarness($runner);

        $payload = [
            'code' => 'function twoSum(nums, target) { return [0, 1]; }',
            'lang' => 'javascript',
        ];

        $this->app->make(BruteForceStage::class)->evaluate($this->makeSession(), $payload);

        Http::assertSent(function ($request) use ($payload) {
            $data = $request->data();

            return $request->url() === 'http://coach.test/coach/critique'
                && ($data['stage'] ?? null) === Stage::BruteForce->value
                && ($data['submission']['code'] ?? null) === $payload['code']
                && ($data['submission']['lang'] ?? null) === $payload['lang']
                && ($data['auto_signals']['compiled'] ?? null) === true
                && ($data['auto_signals']['signature_ok'] ?? null) === true
                && ($data['auto_signals']['tests_passed'] ?? null) === 3
                && ($data['auto_signals']['tests_failed'] ?? null) === 0;
        });
    }

    /**
     * @param  array<string, mixed>  $runner
     * @param  array<string, mixed>  $body
     */
    private function evaluateBruteForce(array $runner, array $body): StageResult
    {
        config(['services.coach.url' => 'http://coach.test']);

        Http::fake([
            'http://coach.test/coach/critique' => Http::response($body),
        ]);

        $this->mockHarness($runner);

        return $this->app->make(BruteForceStage::class)->evaluate($this->makeSession(), [
            'code' => 'function twoSum(nums, target) { return [0, 1]; }',
            'lang' => 'javascript',
        ]);
    }

    /**
     * @param  array<string, mixed>  $runner
     */
    private function mockHarness(array $runner): void
    {
        $this->mock(TestHarnessService::class, function (MockInterface $mock) use ($runner) {
            $mock->shouldReceive('runCode')->once()->andReturn($runner);
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function passingRunner(): array
    {
        return [
            'compiled' => true,
            'signature_ok' => true,
            'tests' => [
                'summary' => [
                    'passed' => 3,
                    'failed' => 0,
                    'total' => 3,
                ],
                'cases' => [],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function failingTestsRunner(): array
    {
        return [
            'compiled' => true,
            'signature_ok' => true,
            'tests' => [
                'summary' => [
                    'passed' => 2,
                    'failed' => 1,
                    'total' => 3,
                ],
                'cases' => [],
            ],
        ];
    }

    private function makeSession(): CoachingSession
    {
        $user = User::factory()->create();
        $problem = Problem::factory()->create();

        return CoachingSession::factory()->for($user)->for($problem)->create([
            'state' => Stage::BruteForce,
            'selected_lang' => 'javascript',
        ]);
    }
}
