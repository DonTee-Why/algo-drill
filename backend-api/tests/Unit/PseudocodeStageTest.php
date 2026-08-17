<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domains\Coach\Rubrics\PseudocodeRubric;
use App\Domains\StateMachine\DTOs\StageResult;
use App\Domains\StateMachine\StageHandlers\PseudocodeStage;
use App\Enums\Stage;
use App\Models\CoachingSession;
use App\Models\Problem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PseudocodeStageTest extends TestCase
{
    use RefreshDatabase;

    public function test_passing_coach_critique_advances_to_brute_force(): void
    {
        $result = $this->evaluatePseudocodeWithResponse([
            'coach_msg' => 'Clear step-by-step plan.',
            'scores' => [
                'step_order' => ['score' => 3, 'max_score' => 3, 'reason' => 'Logical'],
                'bounds' => ['score' => 2, 'max_score' => 3, 'reason' => 'Mostly clear'],
                'edge_handling' => ['score' => 2, 'max_score' => 3, 'reason' => 'Covers empty input'],
            ],
            'flags' => ['too_vague' => false, 'missing_edge_handling' => true],
            'questions' => ['What happens when the array is empty?'],
        ]);

        $this->assertSame(Stage::Pseudocode, $result->stage);
        $this->assertSame('coach', $result->evaluator);
        $this->assertSame(7, $result->totalScore);
        $this->assertSame(PseudocodeRubric::PASS_THRESHOLD, $result->passThreshold);
        $this->assertTrue($result->passed);
        $this->assertSame(Stage::BruteForce, $result->nextState);
        $this->assertSame('Clear step-by-step plan.', $result->coachMsg);
        $this->assertTrue($result->flags['missing_edge_handling']);
        $this->assertSame(['What happens when the array is empty?'], $result->questions);
        $this->assertSame(3, $result->rubricScores['step_order']['score']);
        $this->assertSame('coach', $result->rubricScores['step_order']['by']);
    }

    public function test_failing_coach_critique_stays_in_pseudocode(): void
    {
        $result = $this->evaluatePseudocodeWithResponse([
            'coach_msg' => 'Order the steps more clearly.',
            'scores' => [
                'step_order' => ['score' => 1, 'max_score' => 3, 'reason' => 'Jumbled'],
                'bounds' => ['score' => 1, 'max_score' => 3, 'reason' => 'Unclear'],
                'edge_handling' => ['score' => 1, 'max_score' => 3, 'reason' => 'Missing'],
            ],
            'flags' => ['too_vague' => true],
            'questions' => ['What is the first step, in one sentence?'],
        ]);

        $this->assertFalse($result->passed);
        $this->assertSame(3, $result->totalScore);
        $this->assertSame(Stage::Pseudocode, $result->nextState);
        $this->assertSame('Order the steps more clearly.', $result->coachMsg);
        $this->assertTrue($result->flags['too_vague']);
    }

    public function test_unavailable_coach_does_not_advance(): void
    {
        $result = $this->evaluatePseudocodeWithResponse([
            'coach_msg' => 'I could not reliably evaluate this response.',
            'scores' => [
                'step_order' => ['score' => 0, 'max_score' => 3, 'reason' => 'Unavailable due to invalid model response'],
                'bounds' => ['score' => 0, 'max_score' => 3, 'reason' => 'Unavailable due to invalid model response'],
                'edge_handling' => ['score' => 0, 'max_score' => 3, 'reason' => 'Unavailable due to invalid model response'],
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
        $this->assertSame(Stage::Pseudocode, $result->nextState);
        $this->assertSame([], $result->flags);
        $this->assertSame([], $result->questions);
        $this->assertStringContainsString('temporarily unavailable', (string) $result->coachMsg);
    }

    public function test_forwards_steps_text_to_coach(): void
    {
        config(['services.coach.url' => 'http://coach.test']);

        Http::fake([
            'http://coach.test/coach/critique' => Http::response([
                'coach_msg' => 'Clear step-by-step plan.',
                'scores' => [
                    'step_order' => ['score' => 3, 'max_score' => 3, 'reason' => 'Clear'],
                    'bounds' => ['score' => 3, 'max_score' => 3, 'reason' => 'Clear'],
                    'edge_handling' => ['score' => 3, 'max_score' => 3, 'reason' => 'Clear'],
                ],
                'flags' => ['too_vague' => false],
                'questions' => [],
            ]),
        ]);

        $payload = [
            'steps_text' => 'Walk the array, store seen values, return when the complement exists.',
        ];

        $this->makePseudocodeStage()->evaluate($this->makeSession(), $payload);

        Http::assertSent(function ($request) use ($payload) {
            $data = $request->data();

            return $request->url() === 'http://coach.test/coach/critique'
                && ($data['stage'] ?? null) === Stage::Pseudocode->value
                && ($data['submission']['steps_text'] ?? null) === $payload['steps_text'];
        });
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private function evaluatePseudocodeWithResponse(array $body): StageResult
    {
        config(['services.coach.url' => 'http://coach.test']);

        Http::fake([
            'http://coach.test/coach/critique' => Http::response($body),
        ]);

        return $this->makePseudocodeStage()->evaluate($this->makeSession(), [
            'steps_text' => 'Walk the array, store seen values, return when the complement exists.',
        ]);
    }

    private function makePseudocodeStage(): PseudocodeStage
    {
        return $this->app->make(PseudocodeStage::class);
    }

    private function makeSession(): CoachingSession
    {
        $user = User::factory()->create();
        $problem = Problem::factory()->create();

        return CoachingSession::factory()->for($user)->for($problem)->create([
            'state' => Stage::Pseudocode,
        ]);
    }
}
