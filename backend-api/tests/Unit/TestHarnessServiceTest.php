<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\Lang;
use App\Models\CoachingSession;
use App\Models\Problem;
use App\Models\ProblemSignature;
use App\Models\ProblemTest;
use App\Models\TestRun;
use App\Models\User;
use App\Services\PistonClient;
use App\Services\TestHarnessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TestHarnessServiceTest extends TestCase
{
    use RefreshDatabase;

    private TestHarnessService $service;

    private PistonClient $pistonClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pistonClient = new PistonClient;
        $this->service = new TestHarnessService($this->pistonClient);
    }

    public function test_run_code_success_with_javascript(): void
    {
        $user = User::factory()->create();
        $problem = Problem::factory()->create();
        $session = CoachingSession::factory()->for($user)->for($problem)->create([
            'selected_lang' => 'javascript',
        ]);

        $signature = ProblemSignature::factory()->for($problem)->create([
            'lang' => Lang::Javascript,
            'function_name' => 'twoSum',
            'params' => [
                ['name' => 'nums', 'type' => 'number[]'],
                ['name' => 'target', 'type' => 'number'],
            ],
            'returns' => ['type' => 'number[]'],
        ]);

        $test1 = ProblemTest::factory()->for($problem)->create([
            'input' => ['nums' => [2, 7, 11, 15], 'target' => 9],
            'expected' => [0, 1],
            'is_edge' => false,
            'weight' => 1,
        ]);

        $test2 = ProblemTest::factory()->for($problem)->create([
            'input' => ['nums' => [3, 2, 4], 'target' => 6],
            'expected' => [1, 2],
            'is_edge' => false,
            'weight' => 1,
        ]);

        $userCode = <<<'JS'
function twoSum(nums, target) {
    const map = new Map();
    for (let i = 0; i < nums.length; i++) {
        const complement = target - nums[i];
        if (map.has(complement)) {
            return [map.get(complement), i];
        }
        map.set(nums[i], i);
    }
    return [];
}
JS;

        // Mock Piston response
        Http::fake([
            config('services.piston.url').'/api/v2/execute' => Http::response([
                'language' => 'javascript',
                'version' => '18.17.0',
                'run' => [
                    'stdout' => '{"summary":{"passed":2,"failed":0,"total":2},"cases":[{"status":"passed","input":{"nums":[2,7,11,15],"target":9},"expected":[0,1],"got":[0,1]},{"status":"passed","input":{"nums":[3,2,4],"target":6},"expected":[1,2],"got":[1,2]}]}',
                    'stderr' => '',
                    'code' => 0,
                    'signal' => null,
                    'status' => null,
                    'cpu_time' => 5,
                    'memory' => 1000000,
                ],
            ], 200),
        ]);

        $result = $this->service->runCode($session, 'javascript', $userCode);

        $this->assertTrue($result['compiled']);
        $this->assertTrue($result['signature_ok']);
        $this->assertEquals(2, $result['tests']['summary']['passed']);
        $this->assertEquals(0, $result['tests']['summary']['failed']);
        $this->assertEquals(2, $result['tests']['summary']['total']);
        $this->assertCount(2, $result['tests']['cases']);

        // Verify TestRun was persisted
        $this->assertDatabaseHas('test_runs', [
            'coaching_session_id' => $session->id,
            'lang' => Lang::Javascript->value,
        ]);

        $testRun = TestRun::where('coaching_session_id', $session->id)->first();
        $this->assertNotNull($testRun);
        $this->assertEquals($userCode, $testRun->source);
        $this->assertEquals(2, $testRun->result['summary']['passed']);
    }

    public function test_run_code_handles_compilation_error(): void
    {
        $user = User::factory()->create();
        $problem = Problem::factory()->create();
        $session = CoachingSession::factory()->for($user)->for($problem)->create([
            'selected_lang' => 'javascript',
        ]);

        $signature = ProblemSignature::factory()->for($problem)->create([
            'lang' => Lang::Javascript,
            'function_name' => 'twoSum',
            'params' => [
                ['name' => 'nums', 'type' => 'number[]'],
                ['name' => 'target', 'type' => 'number'],
            ],
            'returns' => ['type' => 'number[]'],
        ]);

        ProblemTest::factory()->for($problem)->create([
            'input' => ['nums' => [2, 7], 'target' => 9],
            'expected' => [0, 1],
            'is_edge' => false,
            'weight' => 1,
        ]);

        $invalidCode = 'function twoSum(nums, target { return []; }'; // Missing closing paren

        // Mock Piston response with compile error
        Http::fake([
            config('services.piston.url').'/api/v2/execute' => Http::response([
                'language' => 'javascript',
                'version' => '18.17.0',
                'compile' => [
                    'stdout' => '',
                    'stderr' => 'SyntaxError: Unexpected token',
                    'code' => 1,
                    'signal' => null,
                ],
                'run' => [
                    'stdout' => '',
                    'stderr' => '',
                    'code' => 0,
                ],
            ], 200),
        ]);

        $result = $this->service->runCode($session, 'javascript', $invalidCode);

        $this->assertFalse($result['compiled']);
        $this->assertTrue($result['signature_ok']); // Signature check is basic string match
        $this->assertEquals(0, $result['tests']['summary']['passed']);
        $this->assertArrayHasKey('compile_error', $result['tests']);
    }

    public function test_run_code_handles_runtime_error(): void
    {
        $user = User::factory()->create();
        $problem = Problem::factory()->create();
        $session = CoachingSession::factory()->for($user)->for($problem)->create([
            'selected_lang' => 'javascript',
        ]);

        $signature = ProblemSignature::factory()->for($problem)->create([
            'lang' => Lang::Javascript,
            'function_name' => 'twoSum',
            'params' => [
                ['name' => 'nums', 'type' => 'number[]'],
                ['name' => 'target', 'type' => 'number'],
            ],
            'returns' => ['type' => 'number[]'],
        ]);

        ProblemTest::factory()->for($problem)->create([
            'input' => ['nums' => [2, 7], 'target' => 9],
            'expected' => [0, 1],
            'is_edge' => false,
            'weight' => 1,
        ]);

        $codeWithError = <<<'JS'
function twoSum(nums, target) {
    throw new Error('Test error');
}
JS;

        // Mock Piston response with runtime error
        Http::fake([
            config('services.piston.url').'/api/v2/execute' => Http::response([
                'language' => 'javascript',
                'version' => '18.17.0',
                'run' => [
                    'stdout' => '',
                    'stderr' => 'Error: Test error',
                    'code' => 1,
                    'signal' => null,
                    'status' => 'RE',
                    'cpu_time' => 1,
                    'memory' => 500000,
                ],
            ], 200),
        ]);

        $result = $this->service->runCode($session, 'javascript', $codeWithError);

        $this->assertTrue($result['compiled']);
        $this->assertArrayHasKey('runtime_error', $result['tests']);
        $this->assertEquals(0, $result['tests']['summary']['passed']);
    }

    public function test_run_code_handles_timeout(): void
    {
        $user = User::factory()->create();
        $problem = Problem::factory()->create();
        $session = CoachingSession::factory()->for($user)->for($problem)->create([
            'selected_lang' => 'javascript',
        ]);

        $signature = ProblemSignature::factory()->for($problem)->create([
            'lang' => Lang::Javascript,
            'function_name' => 'twoSum',
            'params' => [
                ['name' => 'nums', 'type' => 'number[]'],
                ['name' => 'target', 'type' => 'number'],
            ],
            'returns' => ['type' => 'number[]'],
        ]);

        ProblemTest::factory()->for($problem)->create([
            'input' => ['nums' => [2, 7], 'target' => 9],
            'expected' => [0, 1],
            'is_edge' => false,
            'weight' => 1,
        ]);

        $code = 'function twoSum(nums, target) { return []; }';

        // Mock Piston response with timeout
        Http::fake([
            config('services.piston.url').'/api/v2/execute' => Http::response([
                'language' => 'javascript',
                'version' => '18.17.0',
                'run' => [
                    'stdout' => '',
                    'stderr' => '',
                    'code' => null,
                    'signal' => 'SIGKILL',
                    'status' => 'TO',
                    'cpu_time' => 5000,
                    'memory' => 1000000,
                ],
            ], 200),
        ]);

        $result = $this->service->runCode($session, 'javascript', $code);

        $this->assertTrue($result['compiled']);
        $this->assertArrayHasKey('runtime_error', $result['tests']);
        $this->assertStringContainsString('timeout', strtolower($result['tests']['runtime_error'] ?? ''));
    }

    public function test_run_code_handles_missing_signature(): void
    {
        $user = User::factory()->create();
        $problem = Problem::factory()->create();
        $session = CoachingSession::factory()->for($user)->for($problem)->create([
            'selected_lang' => 'python',
        ]);

        // No signature created for Python

        $result = $this->service->runCode($session, 'python', 'def solution(): pass');

        $this->assertFalse($result['compiled']);
        $this->assertFalse($result['signature_ok']);
        $this->assertArrayHasKey('error', $result['tests']);
        $this->assertStringContainsString('No signature found', $result['tests']['error']);
    }

    public function test_run_code_handles_missing_tests(): void
    {
        $user = User::factory()->create();
        $problem = Problem::factory()->create();
        $session = CoachingSession::factory()->for($user)->for($problem)->create([
            'selected_lang' => 'javascript',
        ]);

        ProblemSignature::factory()->for($problem)->create([
            'lang' => Lang::Javascript,
            'function_name' => 'twoSum',
            'params' => [],
            'returns' => ['type' => 'number[]'],
        ]);

        // No tests created

        $result = $this->service->runCode($session, 'javascript', 'function twoSum() { return []; }');

        $this->assertFalse($result['compiled']);
        $this->assertArrayHasKey('error', $result['tests']);
        $this->assertStringContainsString('No tests found', $result['tests']['error']);
    }

    public function test_run_code_handles_piston_api_failure(): void
    {
        $user = User::factory()->create();
        $problem = Problem::factory()->create();
        $session = CoachingSession::factory()->for($user)->for($problem)->create([
            'selected_lang' => 'javascript',
        ]);

        ProblemSignature::factory()->for($problem)->create([
            'lang' => Lang::Javascript,
            'function_name' => 'twoSum',
            'params' => [],
            'returns' => ['type' => 'number[]'],
        ]);

        ProblemTest::factory()->for($problem)->create([
            'input' => ['nums' => [2, 7], 'target' => 9],
            'expected' => [0, 1],
            'is_edge' => false,
            'weight' => 1,
        ]);

        // Mock Piston API failure
        Http::fake([
            config('services.piston.url').'/api/v2/execute' => Http::response([
                'message' => 'Internal server error',
            ], 500),
        ]);

        $result = $this->service->runCode($session, 'javascript', 'function twoSum() { return []; }');

        $this->assertFalse($result['compiled']);
        $this->assertArrayHasKey('error', $result['tests']);
    }

    public function test_run_code_persists_test_run_with_metrics(): void
    {
        $user = User::factory()->create();
        $problem = Problem::factory()->create();
        $session = CoachingSession::factory()->for($user)->for($problem)->create([
            'selected_lang' => 'javascript',
        ]);

        $signature = ProblemSignature::factory()->for($problem)->create([
            'lang' => Lang::Javascript,
            'function_name' => 'twoSum',
            'params' => [
                ['name' => 'nums', 'type' => 'number[]'],
                ['name' => 'target', 'type' => 'number'],
            ],
            'returns' => ['type' => 'number[]'],
        ]);

        ProblemTest::factory()->for($problem)->create([
            'input' => ['nums' => [2, 7], 'target' => 9],
            'expected' => [0, 1],
            'is_edge' => false,
            'weight' => 1,
        ]);

        $userCode = 'function twoSum(nums, target) { return [0, 1]; }';

        // Mock Piston response with metrics
        Http::fake([
            config('services.piston.url').'/api/v2/execute' => Http::response([
                'language' => 'javascript',
                'version' => '18.17.0',
                'run' => [
                    'stdout' => '{"summary":{"passed":1,"failed":0,"total":1},"cases":[{"status":"passed"}]}',
                    'stderr' => '',
                    'code' => 0,
                    'signal' => null,
                    'status' => null,
                    'cpu_time' => 10,
                    'memory' => 2048000, // 2MB in bytes
                ],
            ], 200),
        ]);

        // Test with isSubmission = false (default)
        $this->service->runCode($session, 'javascript', $userCode);

        $testRun = TestRun::where('coaching_session_id', $session->id)->first();
        $this->assertNotNull($testRun);
        $this->assertEquals(10, $testRun->cpu_ms);
        $this->assertEquals(2000, $testRun->mem_kb); // 2MB = 2000KB
        $this->assertFalse($testRun->stderr_truncated);
        $this->assertFalse($testRun->is_submission);

        // Test with isSubmission = true
        $this->service->runCode($session, 'javascript', $userCode, isSubmission: true);

        $submissionRun = TestRun::where('coaching_session_id', $session->id)
            ->where('is_submission', true)
            ->first();
        $this->assertNotNull($submissionRun);
        $this->assertTrue($submissionRun->is_submission);
    }
}
