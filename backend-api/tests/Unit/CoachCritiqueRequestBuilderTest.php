<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domains\Coach\Builders\CoachCritiqueRequestBuilder;
use App\Domains\Coach\DTOs\CoachConstraints;
use App\Domains\Coach\DTOs\CoachCritiqueRequest;
use App\Domains\Coach\DTOs\ProblemContext;
use App\Enums\Lang;
use App\Enums\Stage;
use App\Models\CoachingSession;
use App\Models\Problem;
use App\Models\ProblemSignature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoachCritiqueRequestBuilderTest extends TestCase
{
    use RefreshDatabase;

    private CoachCritiqueRequestBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->builder = $this->app->make(CoachCritiqueRequestBuilder::class);
    }

    public function test_builds_clarify_payload_with_snake_case_keys(): void
    {
        $user = User::factory()->create();
        $problem = Problem::factory()->create([
            'title' => 'Two Sum',
            'tags' => ['array', 'hash-table'],
            'constraints' => ['Each input has exactly one solution.'],
            'description_md' => 'Given an array of integers...',
        ]);

        ProblemSignature::factory()->for($problem)->create([
            'lang' => Lang::Javascript,
            'function_name' => 'twoSum',
            'params' => [
                ['name' => 'nums', 'type' => 'number[]'],
                ['name' => 'target', 'type' => 'number'],
            ],
            'returns' => ['type' => 'number[]', 'meaning' => 'indices of the two numbers'],
        ]);

        $session = CoachingSession::factory()->for($user)->for($problem)->create([
            'selected_lang' => 'javascript',
        ]);

        $payload = [
            'inputs_outputs' => 'Input is nums and target. Output is indices.',
            'constraints' => 'Do not reuse the same element.',
            'examples' => "Example 1: nums = [2,7], target = 9\nExample 2 (edge case): nums = [3,3], target = 6",
        ];

        $request = $this->builder->build($session, Stage::Clarify, $payload);

        $this->assertInstanceOf(CoachCritiqueRequest::class, $request);
        $this->assertInstanceOf(ProblemContext::class, $request->problemContext);
        $this->assertInstanceOf(CoachConstraints::class, $request->coachConstraints);
        $this->assertSame($session->id, $request->sessionId);
        $this->assertSame(Stage::Clarify, $request->stage);
        $this->assertSame('Two Sum', $request->problemContext->title);
        $this->assertSame('twoSum', $request->problemContext->signature?->functionName);
        $this->assertTrue($request->coachConstraints->noCode);

        $array = $request->toArray();

        $this->assertSame('CLARIFY', $array['stage']);
        $this->assertArrayHasKey('rubric', $array);
        $this->assertSame('Two Sum', $array['problem_context']['title']);
        $this->assertSame('twoSum', $array['problem_context']['signature']['function_name']);
        $this->assertSame($payload['inputs_outputs'], $array['submission']['inputs_outputs']);
        $this->assertContains('nums', $array['auto_signals']['mentioned_param_names']);
        $this->assertContains('target', $array['auto_signals']['mentioned_param_names']);
        $this->assertTrue($array['auto_signals']['has_marked_edge_case']);
        $this->assertTrue($array['coach_constraints']['no_code']);
    }

    public function test_builds_approach_payload_with_structured_fields(): void
    {
        $user = User::factory()->create();
        $problem = Problem::factory()->create([
            'title' => 'Two Sum',
            'tags' => ['array', 'hash-table'],
            'constraints' => ['Each input has exactly one solution.'],
            'description_md' => 'Given an array of integers...',
        ]);
        $session = CoachingSession::factory()->for($user)->for($problem)->create([
            'selected_lang' => 'javascript',
        ]);

        $payload = [
            'strategy' => 'Scan once and store seen values.',
            'justification' => 'The complement is recoverable from values already seen.',
            'complexity' => 'Time O(n), space O(n).',
        ];

        $request = $this->builder->build($session, Stage::Approach, $payload);
        $array = $request->toArray();

        $this->assertSame('APPROACH', $array['stage']);
        $this->assertSame($payload['strategy'], $array['submission']['strategy']);
        $this->assertSame($payload['justification'], $array['submission']['justification']);
        $this->assertSame($payload['complexity'], $array['submission']['complexity']);
        $this->assertArrayNotHasKey('text', $array['submission']);
    }

    public function test_builds_pseudocode_payload_from_steps_text(): void
    {
        $session = $this->makeSession();
        $payload = [
            'steps_text' => 'Walk the array, store seen values, return when the complement exists.',
        ];

        $array = $this->builder->build($session, Stage::Pseudocode, $payload)->toArray();

        $this->assertSame('PSEUDOCODE', $array['stage']);
        $this->assertSame($payload['steps_text'], $array['submission']['steps_text']);
        $this->assertArrayNotHasKey('text', $array['submission']);
    }

    public function test_builds_pseudocode_payload_from_text_fallback(): void
    {
        $session = $this->makeSession();
        $payload = [
            'text' => 'Initialize a map, then scan once.',
        ];

        $array = $this->builder->build($session, Stage::Pseudocode, $payload)->toArray();

        $this->assertSame($payload['text'], $array['submission']['steps_text']);
    }

    public function test_builds_brute_force_payload_with_runner_auto_signals(): void
    {
        $session = $this->makeSession();
        $payload = [
            'code' => 'function twoSum(nums, target) { return []; }',
            'lang' => 'javascript',
            'runner' => [
                'compiled' => true,
                'signature_ok' => true,
                'tests' => [
                    'summary' => [
                        'passed' => 3,
                        'failed' => 0,
                        'total' => 3,
                    ],
                ],
            ],
        ];

        $array = $this->builder->build($session, Stage::BruteForce, $payload)->toArray();

        $this->assertSame('BRUTE_FORCE', $array['stage']);
        $this->assertSame($payload['code'], $array['submission']['code']);
        $this->assertSame($payload['lang'], $array['submission']['lang']);
        $this->assertTrue($array['auto_signals']['compiled']);
        $this->assertTrue($array['auto_signals']['signature_ok']);
        $this->assertSame(3, $array['auto_signals']['tests_passed']);
        $this->assertSame(0, $array['auto_signals']['tests_failed']);
        $this->assertSame(3, $array['auto_signals']['tests_total']);
    }

    private function makeSession(): CoachingSession
    {
        $user = User::factory()->create();
        $problem = Problem::factory()->create([
            'title' => 'Two Sum',
            'tags' => ['array', 'hash-table'],
            'constraints' => ['Each input has exactly one solution.'],
            'description_md' => 'Given an array of integers...',
        ]);

        return CoachingSession::factory()->for($user)->for($problem)->create([
            'selected_lang' => 'javascript',
        ]);
    }
}
