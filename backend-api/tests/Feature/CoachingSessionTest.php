<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Stage;
use App\Models\Problem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CoachingSessionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.coach.url' => 'http://coach.test']);

        Http::fake([
            'http://coach.test/coach/critique' => function ($request) {
                $stage = $request['stage'] ?? null;

                if ($stage === Stage::Pseudocode->value) {
                    return Http::response([
                        'coach_msg' => 'Clear step-by-step plan.',
                        'scores' => [
                            'step_order' => ['score' => 3, 'max_score' => 3, 'reason' => 'Logical'],
                            'bounds' => ['score' => 2, 'max_score' => 3, 'reason' => 'Mostly clear'],
                            'edge_handling' => ['score' => 2, 'max_score' => 3, 'reason' => 'Covers empty input'],
                        ],
                        'flags' => [
                            'too_vague' => false,
                            'missing_edge_handling' => false,
                            'code_leak_blocked' => false,
                            'prompt_injection_detected' => false,
                        ],
                        'questions' => [],
                    ]);
                }

                if ($stage === Stage::Approach->value) {
                    return Http::response([
                        'coach_msg' => 'Solid high-level plan.',
                        'scores' => [
                            'strategy' => ['score' => 2, 'max_score' => 2, 'reason' => 'Clear'],
                            'justification' => ['score' => 2, 'max_score' => 2, 'reason' => 'Clear'],
                            'complexity' => ['score' => 2, 'max_score' => 2, 'reason' => 'Clear'],
                        ],
                        'flags' => [
                            'too_vague' => false,
                            'missing_complexity' => false,
                            'code_leak_blocked' => false,
                            'prompt_injection_detected' => false,
                        ],
                        'questions' => [],
                    ]);
                }

                return Http::response([
                    'coach_msg' => 'Good clarification.',
                    'scores' => [
                        'inputs_outputs' => ['score' => 3, 'max_score' => 3, 'reason' => 'Clear'],
                        'constraints' => ['score' => 3, 'max_score' => 3, 'reason' => 'Clear'],
                        'examples' => ['score' => 6, 'max_score' => 6, 'reason' => 'Includes edge case'],
                    ],
                    'flags' => [
                        'too_vague' => false,
                        'output_contract_confused' => false,
                        'values_vs_indices_confusion' => false,
                        'missing_edge_case' => false,
                        'code_leak_blocked' => false,
                        'prompt_injection_detected' => false,
                    ],
                    'questions' => [],
                ]);
            },
        ]);
    }

    public function test_authenticated_user_can_create_coaching_session(): void
    {
        $user = User::factory()->create();
        $problem = Problem::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('sessions.store'), [
                'problem_id' => $problem->id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('coaching_sessions', [
            'user_id' => $user->id,
            'problem_id' => $problem->id,
            'state' => Stage::Clarify->value,
        ]);
    }

    public function test_guest_cannot_create_coaching_session(): void
    {
        $problem = Problem::factory()->create();

        $response = $this->post(route('sessions.store'), [
            'problem_id' => $problem->id,
        ]);

        $response->assertRedirect(route('login'));

        $this->assertDatabaseCount('coaching_sessions', 0);
    }

    public function test_creating_session_requires_valid_problem_id(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('sessions.store'), [
                'problem_id' => 'invalid-uuid',
            ]);

        $response->assertSessionHasErrors('problem_id');

        $this->assertDatabaseCount('coaching_sessions', 0);
    }

    public function test_creating_session_requires_existing_problem(): void
    {
        $user = User::factory()->create();
        $fakeUuid = '12345678-1234-1234-1234-123456789012';

        $response = $this->actingAs($user)
            ->post(route('sessions.store'), [
                'problem_id' => $fakeUuid,
            ]);

        $response->assertSessionHasErrors('problem_id');

        $this->assertDatabaseCount('coaching_sessions', 0);
    }

    public function test_user_can_submit_clarify_stage(): void
    {
        $user = User::factory()->create();
        $problem = Problem::factory()->create();
        $session = $user->coachingSessions()->create([
            'problem_id' => $problem->id,
            'state' => Stage::Clarify,
            'selected_lang' => 'javascript',
            'scores' => [],
            'hints_used' => [],
            'timers' => [],
            'revealed_langs' => [],
        ]);

        $response = $this->actingAs($user)
            ->post(route('sessions.submit', $session->id), [
                'stage' => Stage::Clarify->value,
                'payload' => [
                    'inputs_outputs' => 'Input: array of numbers, target. Output: indices of two numbers that sum to target.',
                    'constraints' => 'Array length between 2 and 10^4. Each number is between -10^9 and 10^9.',
                    'examples' => "Example 1: nums = [2,7,11,15], target = 9\nOutput: [0,1]\n\nExample 2 (edge case): nums = [3,3], target = 6\nOutput: [0,1]",
                ],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('attempts', [
            'coaching_session_id' => $session->id,
            'stage' => Stage::Clarify->value,
        ]);

        $session->refresh();
        $this->assertEquals(Stage::Approach->value, $session->state->value);
    }

    public function test_user_can_progress_through_all_text_stages(): void
    {
        $user = User::factory()->create();
        $problem = Problem::factory()->create();
        $session = $user->coachingSessions()->create([
            'problem_id' => $problem->id,
            'state' => Stage::Clarify,
            'selected_lang' => 'javascript',
            'scores' => [],
            'hints_used' => [],
            'timers' => [],
            'revealed_langs' => [],
        ]);

        // Submit CLARIFY
        $this->actingAs($user)
            ->post(route('sessions.submit', $session->id), [
                'stage' => Stage::Clarify->value,
                'payload' => [
                    'inputs_outputs' => 'Input: array of numbers, target. Output: indices.',
                    'constraints' => 'Array length between 2 and 10^4.',
                    'examples' => "Example 1: nums = [2,7], target = 9\nExample 2 (edge case): nums = [3,3], target = 6",
                ],
            ]);
        $session->refresh();
        $this->assertEquals(Stage::Approach->value, $session->state->value);

        // Submit APPROACH
        $this->actingAs($user)
            ->post(route('sessions.submit', $session->id), [
                'stage' => Stage::Approach->value,
                'payload' => [
                    'strategy' => 'Scan once and store seen values in a hash map.',
                    'justification' => 'The complement is recoverable from values already seen.',
                    'complexity' => 'Time O(n), space O(n).',
                ],
            ]);
        $session->refresh();
        $this->assertEquals(Stage::Pseudocode->value, $session->state->value);

        // Submit PSEUDOCODE
        $this->actingAs($user)
            ->post(route('sessions.submit', $session->id), [
                'stage' => Stage::Pseudocode->value,
                'payload' => [
                    'steps_text' => 'Walk the array, store seen values, return when the complement exists.',
                ],
            ]);
        $session->refresh();
        $this->assertEquals(Stage::BruteForce->value, $session->state->value);
    }
}
