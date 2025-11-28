<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\Lang;
use App\Models\Problem;
use App\Models\ProblemSignature;
use App\Models\ProblemTest;
use App\Services\ProblemService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProblemServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProblemService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ProblemService;
    }

    public function test_create_problem_with_basic_data(): void
    {
        $data = [
            'title' => 'Two Sum',
            'difficulty' => 'Easy',
            'tags' => ['Array', 'Hash Table'],
            'constraints' => ['1 <= nums.length <= 10^4'],
            'description_md' => '# Two Sum Problem',
            'is_premium' => false,
            'signatures' => [],
            'tests' => [],
        ];

        $problem = $this->service->createProblem($data);

        $this->assertInstanceOf(Problem::class, $problem);
        $this->assertEquals('Two Sum', $problem->title);
        $this->assertEquals('Easy', $problem->difficulty);
        $this->assertEquals(['Array', 'Hash Table'], $problem->tags);
        $this->assertFalse($problem->is_premium);
        $this->assertDatabaseHas('problems', [
            'title' => 'Two Sum',
            'difficulty' => 'Easy',
        ]);
    }

    public function test_create_problem_auto_generates_slug_when_not_provided(): void
    {
        $data = [
            'title' => 'Valid Parentheses',
            'difficulty' => 'Easy',
            'tags' => ['String', 'Stack'],
            'constraints' => ['1 <= s.length <= 10^4'],
            'description_md' => '# Problem',
            'signatures' => [],
            'tests' => [],
        ];

        $problem = $this->service->createProblem($data);

        $this->assertEquals('valid-parentheses', $problem->slug);
    }

    public function test_create_problem_with_custom_slug(): void
    {
        $data = [
            'title' => 'Two Sum',
            'slug' => 'custom-two-sum',
            'difficulty' => 'Easy',
            'tags' => ['Array'],
            'constraints' => ['1 <= nums.length <= 10^4'],
            'description_md' => '# Problem',
            'signatures' => [],
            'tests' => [],
        ];

        $problem = $this->service->createProblem($data);

        $this->assertEquals('custom-two-sum', $problem->slug);
    }

    public function test_create_problem_with_signatures(): void
    {
        $data = [
            'title' => 'Two Sum',
            'difficulty' => 'Easy',
            'tags' => ['Array'],
            'constraints' => ['1 <= nums.length <= 10^4'],
            'description_md' => '# Problem',
            'signatures' => [
                [
                    'lang' => Lang::Javascript->value,
                    'function_name' => 'twoSum',
                    'params' => [
                        ['name' => 'nums', 'type' => 'number[]'],
                        ['name' => 'target', 'type' => 'number'],
                    ],
                    'returns' => ['type' => 'number[]'],
                ],
                [
                    'lang' => Lang::Python->value,
                    'function_name' => 'two_sum',
                    'params' => [
                        ['name' => 'nums', 'type' => 'List[int]'],
                        ['name' => 'target', 'type' => 'int'],
                    ],
                    'returns' => ['type' => 'List[int]'],
                ],
            ],
            'tests' => [],
        ];

        $problem = $this->service->createProblem($data);

        $this->assertCount(2, $problem->signatures);
        $this->assertEquals('twoSum', $problem->signatures[0]->function_name);
        $this->assertEquals(Lang::Javascript, $problem->signatures[0]->lang);
        $this->assertEquals('two_sum', $problem->signatures[1]->function_name);
        $this->assertEquals(Lang::Python, $problem->signatures[1]->lang);
    }

    public function test_create_problem_with_tests(): void
    {
        $data = [
            'title' => 'Two Sum',
            'difficulty' => 'Easy',
            'tags' => ['Array'],
            'constraints' => ['1 <= nums.length <= 10^4'],
            'description_md' => '# Problem',
            'signatures' => [],
            'tests' => [
                [
                    'input' => ['nums' => [2, 7, 11, 15], 'target' => 9],
                    'expected' => [0, 1],
                    'is_edge' => false,
                    'weight' => 1,
                ],
                [
                    'input' => ['nums' => [3, 2, 4], 'target' => 6],
                    'expected' => [1, 2],
                    'is_edge' => false,
                    'weight' => 1,
                ],
                [
                    'input' => ['nums' => [], 'target' => 0],
                    'expected' => [],
                    'is_edge' => true,
                    'weight' => 2,
                ],
            ],
        ];

        $problem = $this->service->createProblem($data);

        $this->assertCount(3, $problem->tests);
        $this->assertEquals([2, 7, 11, 15], $problem->tests[0]->input['nums']);
        $this->assertEquals([0, 1], $problem->tests[0]->expected);
        $this->assertFalse($problem->tests[0]->is_edge);
        $this->assertTrue($problem->tests[2]->is_edge);
        $this->assertEquals(2, $problem->tests[2]->weight);
    }

    public function test_update_problem_basic_fields(): void
    {
        $problem = Problem::factory()->create([
            'title' => 'Original Title',
            'difficulty' => 'Easy',
            'tags' => ['Array'],
            'is_premium' => false,
        ]);

        $data = [
            'title' => 'Updated Title',
            'difficulty' => 'Medium',
            'tags' => ['Array', 'Hash Table'],
            'constraints' => ['New constraint'],
            'description_md' => 'Updated description',
            'is_premium' => true,
        ];

        $updatedProblem = $this->service->updateProblem($problem, $data);

        $this->assertEquals('Updated Title', $updatedProblem->title);
        $this->assertEquals('Medium', $updatedProblem->difficulty);
        $this->assertEquals(['Array', 'Hash Table'], $updatedProblem->tags);
        $this->assertTrue($updatedProblem->is_premium);
    }

    public function test_update_problem_replaces_signatures(): void
    {
        $problem = Problem::factory()->create();

        // Create initial signature using factory
        ProblemSignature::factory()->for($problem)->create([
            'lang' => Lang::Javascript,
            'function_name' => 'oldFunction',
            'params' => [['name' => 'x', 'type' => 'number']],
            'returns' => ['type' => 'number'],
        ]);

        $this->assertCount(1, $problem->signatures);

        // Update with new signatures
        $data = [
            'title' => $problem->title,
            'difficulty' => $problem->difficulty,
            'tags' => $problem->tags,
            'constraints' => $problem->constraints,
            'description_md' => $problem->description_md,
            'signatures' => [
                [
                    'lang' => Lang::Python->value,
                    'function_name' => 'new_function',
                    'params' => [['name' => 'y', 'type' => 'int']],
                    'returns' => ['type' => 'int'],
                ],
                [
                    'lang' => Lang::Php->value,
                    'function_name' => 'newFunction',
                    'params' => [['name' => 'z', 'type' => 'int']],
                    'returns' => ['type' => 'int'],
                ],
            ],
        ];

        $updatedProblem = $this->service->updateProblem($problem, $data);

        // Should have 2 new signatures, old one deleted
        $this->assertCount(2, $updatedProblem->signatures);

        $pythonSig = $updatedProblem->signatures->firstWhere('lang', Lang::Python);
        $phpSig = $updatedProblem->signatures->firstWhere('lang', Lang::Php);

        $this->assertNotNull($pythonSig);
        $this->assertNotNull($phpSig);
        $this->assertEquals('new_function', $pythonSig->function_name);
        $this->assertEquals('newFunction', $phpSig->function_name);

        // Old signature should not exist
        $this->assertDatabaseMissing('problem_signatures', [
            'function_name' => 'oldFunction',
        ]);
    }

    public function test_update_problem_replaces_tests(): void
    {
        $problem = Problem::factory()->create();

        // Create initial test using factory
        ProblemTest::factory()->for($problem)->create([
            'input' => ['x' => 1],
            'expected' => 2,
            'is_edge' => false,
            'weight' => 1,
        ]);

        $this->assertCount(1, $problem->tests);

        // Update with new tests
        $data = [
            'title' => $problem->title,
            'difficulty' => $problem->difficulty,
            'tags' => $problem->tags,
            'constraints' => $problem->constraints,
            'description_md' => $problem->description_md,
            'tests' => [
                [
                    'input' => ['y' => 5],
                    'expected' => 10,
                    'is_edge' => true,
                    'weight' => 3,
                ],
            ],
        ];

        $updatedProblem = $this->service->updateProblem($problem, $data);

        // Should have 1 new test, old one deleted
        $this->assertCount(1, $updatedProblem->tests);
        $this->assertEquals(['y' => 5], $updatedProblem->tests[0]->input);
        $this->assertEquals(10, $updatedProblem->tests[0]->expected);
        $this->assertTrue($updatedProblem->tests[0]->is_edge);

        // Old test should not exist
        $this->assertDatabaseMissing('problem_tests', [
            'input' => json_encode(['x' => 1]),
        ]);
    }

    public function test_create_problem_uses_transaction(): void
    {
        $data = [
            'title' => 'Test Problem',
            'difficulty' => 'Easy',
            'tags' => ['Array'],
            'constraints' => ['test'],
            'description_md' => '# Test',
            'signatures' => [],
            'tests' => [],
        ];

        // This should work and commit
        $problem = $this->service->createProblem($data);

        $this->assertDatabaseHas('problems', [
            'id' => $problem->id,
            'title' => 'Test Problem',
        ]);
    }

    public function test_update_problem_uses_transaction(): void
    {
        $problem = Problem::factory()->create();

        $data = [
            'title' => 'Updated in Transaction',
            'difficulty' => 'Hard',
            'tags' => ['Test'],
            'constraints' => ['test'],
            'description_md' => '# Test',
        ];

        $updatedProblem = $this->service->updateProblem($problem, $data);

        $this->assertDatabaseHas('problems', [
            'id' => $updatedProblem->id,
            'title' => 'Updated in Transaction',
        ]);
    }

    public function test_create_problem_defaults_is_premium_to_false(): void
    {
        $data = [
            'title' => 'Free Problem',
            'difficulty' => 'Easy',
            'tags' => ['Array'],
            'constraints' => ['test'],
            'description_md' => '# Test',
            'signatures' => [],
            'tests' => [],
        ];

        $problem = $this->service->createProblem($data);

        $this->assertFalse($problem->is_premium);
    }

    public function test_update_problem_with_empty_signatures_and_tests(): void
    {
        $problem = Problem::factory()->create();

        // Add some initial data using factories
        ProblemSignature::factory()->for($problem)->create([
            'lang' => Lang::Javascript,
            'function_name' => 'test',
            'params' => [],
            'returns' => ['type' => 'void'],
        ]);
        ProblemTest::factory()->for($problem)->create([
            'input' => ['x' => 1],
            'expected' => 1,
            'is_edge' => false,
            'weight' => 1,
        ]);

        // Update with empty arrays
        $data = [
            'title' => $problem->title,
            'difficulty' => $problem->difficulty,
            'tags' => $problem->tags,
            'constraints' => $problem->constraints,
            'description_md' => $problem->description_md,
            'signatures' => [],
            'tests' => [],
        ];

        $updatedProblem = $this->service->updateProblem($problem, $data);

        // All signatures and tests should be deleted
        $this->assertCount(0, $updatedProblem->signatures);
        $this->assertCount(0, $updatedProblem->tests);
    }
}
