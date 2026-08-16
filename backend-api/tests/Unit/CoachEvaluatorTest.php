<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domains\Coach\DTOs\CoachCritiqueResult;
use App\Domains\Evaluator\CoachEvaluator;
use App\Enums\Stage;
use App\Models\CoachingSession;
use App\Models\Problem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CoachEvaluatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_evaluate_maps_successful_coach_response(): void
    {
        config(['services.coach.url' => 'http://coach.test']);

        Http::fake([
            'http://coach.test/coach/critique' => Http::response([
                'coach_msg' => 'Tighten the output wording.',
                'scores' => [
                    'inputs_outputs' => ['score' => 2, 'max_score' => 3, 'reason' => 'Mostly clear'],
                    'constraints' => ['score' => 3, 'max_score' => 3, 'reason' => 'Good'],
                    'examples' => ['score' => 6, 'max_score' => 6, 'reason' => 'Good examples'],
                ],
                'flags' => ['too_vague' => false],
                'questions' => ['Can you be more precise about the return value?'],
            ]),
        ]);

        $user = User::factory()->create();
        $problem = Problem::factory()->create();
        $session = CoachingSession::factory()->for($user)->for($problem)->create();

        $evaluator = $this->app->make(CoachEvaluator::class);

        $result = $evaluator->evaluate(Stage::Clarify, [
            'inputs_outputs' => 'nums and target in, indices out',
            'constraints' => 'one solution',
            'examples' => "Example 1\nExample 2 edge case",
        ], $session);

        $this->assertTrue($result->available);
        $this->assertSame('Tighten the output wording.', $result->coachMsg);
        $this->assertSame(2, $result->scores['inputs_outputs']['score']);
        $this->assertSame('coach', $result->scores['inputs_outputs']['by']);
        $this->assertFalse($result->flags['too_vague']);
        $this->assertCount(1, $result->questions);
    }

    public function test_evaluate_gracefully_falls_back_when_service_fails(): void
    {
        config(['services.coach.url' => 'http://coach.test']);

        Http::fake([
            'http://coach.test/coach/critique' => Http::response(['message' => 'down'], 503),
        ]);

        $user = User::factory()->create();
        $problem = Problem::factory()->create();
        $session = CoachingSession::factory()->for($user)->for($problem)->create();

        $evaluator = $this->app->make(CoachEvaluator::class);

        $result = $evaluator->evaluate(Stage::Clarify, [
            'inputs_outputs' => 'nums and target in, indices out',
            'constraints' => 'one solution',
            'examples' => "Example 1\nExample 2 edge case",
        ], $session);

        $this->assertFalse($result->available);
        $this->assertSame([], $result->scores);
        $this->assertStringContainsString('temporarily unavailable', (string) $result->coachMsg);
    }

    public function test_evaluate_treats_fallback_used_as_unavailable(): void
    {
        $result = $this->evaluateClarifyWithFlags([
            'too_vague' => false,
            'fallback_used' => true,
            'invalid_json' => false,
        ]);

        $this->assertFalse($result->available);
        $this->assertSame([], $result->scores);
        $this->assertSame([], $result->flags);
        $this->assertSame([], $result->questions);
        $this->assertStringContainsString('temporarily unavailable', (string) $result->coachMsg);
    }

    public function test_evaluate_treats_invalid_json_as_unavailable(): void
    {
        $result = $this->evaluateClarifyWithFlags([
            'too_vague' => false,
            'fallback_used' => false,
            'invalid_json' => true,
        ]);

        $this->assertFalse($result->available);
        $this->assertSame([], $result->scores);
        $this->assertStringContainsString('temporarily unavailable', (string) $result->coachMsg);
    }

    public function test_evaluate_keeps_partial_scores_when_only_missing_scores_is_set(): void
    {
        $result = $this->evaluateClarifyWithFlags(
            [
                'too_vague' => false,
                'fallback_used' => false,
                'invalid_json' => false,
                'missing_scores' => true,
            ],
            [
                'inputs_outputs' => ['score' => 2, 'max_score' => 3, 'reason' => 'Mostly clear'],
                'constraints' => ['score' => 0, 'max_score' => 3, 'reason' => 'Missing from model response'],
                'examples' => ['score' => 6, 'max_score' => 6, 'reason' => 'Good examples'],
            ],
        );

        $this->assertTrue($result->available);
        $this->assertSame(2, $result->scores['inputs_outputs']['score']);
        $this->assertTrue($result->flags['missing_scores']);
    }

    /**
     * @param  array<string, bool>  $flags
     * @param  array<string, array{score: int, max_score: int, reason: string}>|null  $scores
     */
    private function evaluateClarifyWithFlags(array $flags, ?array $scores = null): CoachCritiqueResult
    {
        config(['services.coach.url' => 'http://coach.test']);

        Http::fake([
            'http://coach.test/coach/critique' => Http::response([
                'coach_msg' => 'I could not reliably evaluate this response.',
                'scores' => $scores ?? [
                    'inputs_outputs' => ['score' => 0, 'max_score' => 3, 'reason' => 'Unavailable due to invalid model response'],
                    'constraints' => ['score' => 0, 'max_score' => 3, 'reason' => 'Unavailable due to invalid model response'],
                    'examples' => ['score' => 0, 'max_score' => 6, 'reason' => 'Unavailable due to invalid model response'],
                ],
                'flags' => $flags,
                'questions' => [],
            ]),
        ]);

        $user = User::factory()->create();
        $problem = Problem::factory()->create();
        $session = CoachingSession::factory()->for($user)->for($problem)->create();

        $evaluator = $this->app->make(CoachEvaluator::class);

        return $evaluator->evaluate(Stage::Clarify, [
            'inputs_outputs' => 'nums and target in, indices out',
            'constraints' => 'one solution',
            'examples' => "Example 1\nExample 2 edge case",
        ], $session);
    }
}
