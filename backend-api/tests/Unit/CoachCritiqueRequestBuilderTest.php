<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domains\Coach\Builders\ClarifyAutoSignalsBuilder;
use App\Domains\Coach\Builders\CoachCritiqueRequestBuilder;
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

        $this->builder = new CoachCritiqueRequestBuilder(new ClarifyAutoSignalsBuilder);
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

        $this->assertSame($session->id, $request['session_id']);
        $this->assertSame('CLARIFY', $request['stage']);
        $this->assertArrayHasKey('rubric', $request);
        $this->assertSame('Two Sum', $request['problem_context']['title']);
        $this->assertSame('twoSum', $request['problem_context']['signature']['function_name']);
        $this->assertSame($payload['inputs_outputs'], $request['submission']['inputs_outputs']);
        $this->assertContains('nums', $request['auto_signals']['mentioned_param_names']);
        $this->assertContains('target', $request['auto_signals']['mentioned_param_names']);
        $this->assertTrue($request['auto_signals']['has_marked_edge_case']);
        $this->assertTrue($request['constraints']['no_code']);
    }
}
