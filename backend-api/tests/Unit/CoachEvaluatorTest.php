<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domains\Coach\Builders\ClarifyAutoSignalsBuilder;
use App\Domains\Coach\Builders\CoachCritiqueRequestBuilder;
use App\Domains\Evaluator\CoachEvaluator;
use App\Enums\Stage;
use App\Models\CoachingSession;
use App\Models\Problem;
use App\Models\User;
use App\Services\CoachClient;
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

        $evaluator = new CoachEvaluator(
            new CoachClient,
            new CoachCritiqueRequestBuilder(new ClarifyAutoSignalsBuilder),
        );

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

        $evaluator = new CoachEvaluator(
            new CoachClient,
            new CoachCritiqueRequestBuilder(new ClarifyAutoSignalsBuilder),
        );

        $result = $evaluator->evaluate(Stage::Clarify, [
            'inputs_outputs' => 'nums and target in, indices out',
            'constraints' => 'one solution',
            'examples' => "Example 1\nExample 2 edge case",
        ], $session);

        $this->assertFalse($result->available);
        $this->assertSame([], $result->scores);
        $this->assertStringContainsString('temporarily unavailable', (string) $result->coachMsg);
    }
}
