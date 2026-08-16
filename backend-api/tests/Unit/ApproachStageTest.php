<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domains\Coach\Rubrics\ApproachRubric;
use App\Domains\StateMachine\DTOs\StageResult;
use App\Domains\StateMachine\StageHandlers\ApproachStage;
use App\Enums\Stage;
use App\Models\CoachingSession;
use App\Models\Problem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ApproachStageTest extends TestCase
{
    use RefreshDatabase;

    public function test_passing_coach_critique_advances_to_pseudocode(): void
    {
        $result = $this->evaluateApproachWithResponse([
            'coach_msg' => 'Clear high-level plan.',
            'scores' => [
                'strategy' => ['score' => 2, 'max_score' => 2, 'reason' => 'Clear idea'],
                'justification' => ['score' => 2, 'max_score' => 2, 'reason' => 'Explains why'],
                'complexity' => ['score' => 1, 'max_score' => 2, 'reason' => 'Time only'],
            ],
            'flags' => ['too_vague' => false, 'missing_complexity' => true],
            'questions' => ['What space cost does that plan imply?'],
        ]);

        $this->assertSame(Stage::Approach, $result->stage);
        $this->assertSame('coach', $result->evaluator);
        $this->assertSame(5, $result->totalScore);
        $this->assertSame(ApproachRubric::PASS_THRESHOLD, $result->passThreshold);
        $this->assertTrue($result->passed);
        $this->assertSame(Stage::Pseudocode, $result->nextState);
        $this->assertSame('Clear high-level plan.', $result->coachMsg);
        $this->assertTrue($result->flags['missing_complexity']);
        $this->assertSame(['What space cost does that plan imply?'], $result->questions);
        $this->assertSame(2, $result->rubricScores['strategy']['score']);
        $this->assertSame('coach', $result->rubricScores['strategy']['by']);
    }

    public function test_failing_coach_critique_stays_in_approach(): void
    {
        $result = $this->evaluateApproachWithResponse([
            'coach_msg' => 'Name the core idea more clearly.',
            'scores' => [
                'strategy' => ['score' => 1, 'max_score' => 2, 'reason' => 'Vague'],
                'justification' => ['score' => 1, 'max_score' => 2, 'reason' => 'Weak'],
                'complexity' => ['score' => 0, 'max_score' => 2, 'reason' => 'Missing'],
            ],
            'flags' => ['too_vague' => true],
            'questions' => ['In one sentence, what is the core idea?'],
        ]);

        $this->assertFalse($result->passed);
        $this->assertSame(2, $result->totalScore);
        $this->assertSame(Stage::Approach, $result->nextState);
        $this->assertSame('Name the core idea more clearly.', $result->coachMsg);
        $this->assertTrue($result->flags['too_vague']);
    }

    public function test_unavailable_coach_does_not_advance(): void
    {
        $result = $this->evaluateApproachWithResponse([
            'coach_msg' => 'I could not reliably evaluate this response.',
            'scores' => [
                'strategy' => ['score' => 0, 'max_score' => 2, 'reason' => 'Unavailable due to invalid model response'],
                'justification' => ['score' => 0, 'max_score' => 2, 'reason' => 'Unavailable due to invalid model response'],
                'complexity' => ['score' => 0, 'max_score' => 2, 'reason' => 'Unavailable due to invalid model response'],
            ],
            'flags' => [
                'fallback_used' => true,
                'invalid_json' => true,
            ],
            'questions' => [],
        ]);

        $this->assertFalse($result->passed);
        $this->assertSame('auto', $result->evaluator);
        $this->assertSame([], $result->rubricScores);
        $this->assertSame(0, $result->totalScore);
        $this->assertSame(Stage::Approach, $result->nextState);
        $this->assertSame([], $result->flags);
        $this->assertSame([], $result->questions);
        $this->assertStringContainsString('temporarily unavailable', (string) $result->coachMsg);
    }

    public function test_forwards_structured_payload_to_coach_unchanged(): void
    {
        config(['services.coach.url' => 'http://coach.test']);

        Http::fake([
            'http://coach.test/coach/critique' => Http::response([
                'coach_msg' => 'Clear high-level plan.',
                'scores' => [
                    'strategy' => ['score' => 2, 'max_score' => 2, 'reason' => 'Clear'],
                    'justification' => ['score' => 2, 'max_score' => 2, 'reason' => 'Clear'],
                    'complexity' => ['score' => 2, 'max_score' => 2, 'reason' => 'Clear'],
                ],
                'flags' => ['too_vague' => false],
                'questions' => [],
            ]),
        ]);

        $payload = [
            'strategy' => 'Scan once and store seen values.',
            'justification' => 'The complement is recoverable from values already seen.',
            'complexity' => 'Time O(n), space O(n).',
        ];

        $this->makeApproachStage()->evaluate($this->makeSession(), $payload);

        Http::assertSent(function ($request) use ($payload) {
            $data = $request->data();

            return $request->url() === 'http://coach.test/coach/critique'
                && ($data['stage'] ?? null) === Stage::Approach->value
                && ($data['submission']['strategy'] ?? null) === $payload['strategy']
                && ($data['submission']['justification'] ?? null) === $payload['justification']
                && ($data['submission']['complexity'] ?? null) === $payload['complexity'];
        });
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private function evaluateApproachWithResponse(array $body): StageResult
    {
        config(['services.coach.url' => 'http://coach.test']);

        Http::fake([
            'http://coach.test/coach/critique' => Http::response($body),
        ]);

        return $this->makeApproachStage()->evaluate($this->makeSession(), [
            'strategy' => 'Scan once and store seen values.',
            'justification' => 'The complement is recoverable from values already seen.',
            'complexity' => 'Time O(n), space O(n).',
        ]);
    }

    private function makeApproachStage(): ApproachStage
    {
        return $this->app->make(ApproachStage::class);
    }

    private function makeSession(): CoachingSession
    {
        $user = User::factory()->create();
        $problem = Problem::factory()->create();

        return CoachingSession::factory()->for($user)->for($problem)->create([
            'state' => Stage::Approach,
        ]);
    }
}
