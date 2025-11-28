<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Problem;
use App\Models\ProblemSignature;
use App\Models\ProblemTest;
use Illuminate\Database\Seeder;

class ProblemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedTwoSum();
        $this->seedValidPalindrome();
        $this->seedMergeTwoSortedLists();
        $this->seedContainsDuplicate();
        $this->seedLongestSubstringWithoutRepeating();
    }

    protected function seedTwoSum(): void
    {
        $problem = Problem::create([
            'title' => 'Two Sum',
            'difficulty' => 'Easy',
            'tags' => ['array', 'hash-table'],
            'constraints' => [
                '2 ≤ nums.length ≤ 10⁴',
                '-10⁹ ≤ nums[i] ≤ 10⁹',
                '-10⁹ ≤ target ≤ 10⁹',
                'Only one valid answer exists',
            ],
            'description_md' => "## Problem\n\nGiven an array of integers `nums` and an integer `target`, return indices of the two numbers that add up to `target`.\n\nYou may assume that each input has exactly one solution, and you may not use the same element twice.\n\n## Examples\n\n**Example 1:**\n```\nInput: nums = [2,7,11,15], target = 9\nOutput: [0,1]\nExplanation: nums[0] + nums[1] = 2 + 7 = 9\n```\n\n**Example 2:**\n```\nInput: nums = [3,2,4], target = 6\nOutput: [1,2]\n```\n\n**Example 3:**\n```\nInput: nums = [3,3], target = 6\nOutput: [0,1]\n```",
            'is_premium' => false,
        ]);

        ProblemSignature::create([
            'problem_id' => $problem->id,
            'lang' => 'javascript',
            'function_name' => 'twoSum',
            'params' => [
                ['name' => 'nums', 'type' => 'number[]'],
                ['name' => 'target', 'type' => 'number'],
            ],
            'returns' => ['type' => 'number[]'],
        ]);

        ProblemSignature::create([
            'problem_id' => $problem->id,
            'lang' => 'python',
            'function_name' => 'twoSum',
            'params' => [
                ['name' => 'nums', 'type' => 'List[int]'],
                ['name' => 'target', 'type' => 'int'],
            ],
            'returns' => ['type' => 'List[int]'],
        ]);

        ProblemTest::create([
            'problem_id' => $problem->id,
            'input' => [[2, 7, 11, 15], 9],
            'expected' => [0, 1],
            'is_edge' => false,
            'weight' => 1,
        ]);

        ProblemTest::create([
            'problem_id' => $problem->id,
            'input' => [[3, 2, 4], 6],
            'expected' => [1, 2],
            'is_edge' => false,
            'weight' => 1,
        ]);

        ProblemTest::create([
            'problem_id' => $problem->id,
            'input' => [[3, 3], 6],
            'expected' => [0, 1],
            'is_edge' => false,
            'weight' => 1,
        ]);

        ProblemTest::create([
            'problem_id' => $problem->id,
            'input' => [[-1, -2, -3, -4, -5], -8],
            'expected' => [2, 4],
            'is_edge' => true,
            'weight' => 2,
        ]);
    }

    protected function seedValidPalindrome(): void
    {
        $problem = Problem::create([
            'title' => 'Valid Palindrome',
            'difficulty' => 'Easy',
            'tags' => ['string', 'two-pointers'],
            'constraints' => [
                '1 ≤ s.length ≤ 2 × 10⁵',
                's consists only of printable ASCII characters',
            ],
            'description_md' => "## Problem\n\nA phrase is a **palindrome** if, after converting all uppercase letters to lowercase and removing all non-alphanumeric characters, it reads the same forward and backward.\n\nGiven a string `s`, return `true` if it is a palindrome, or `false` otherwise.\n\n## Examples\n\n**Example 1:**\n```\nInput: s = \"A man, a plan, a canal: Panama\"\nOutput: true\nExplanation: \"amanaplanacanalpanama\" is a palindrome.\n```\n\n**Example 2:**\n```\nInput: s = \"race a car\"\nOutput: false\nExplanation: \"raceacar\" is not a palindrome.\n```\n\n**Example 3:**\n```\nInput: s = \" \"\nOutput: true\nExplanation: Empty string is a palindrome.\n```",
            'is_premium' => false,
        ]);

        ProblemSignature::create([
            'problem_id' => $problem->id,
            'lang' => 'javascript',
            'function_name' => 'isPalindrome',
            'params' => [
                ['name' => 's', 'type' => 'string'],
            ],
            'returns' => ['type' => 'boolean'],
        ]);

        ProblemSignature::create([
            'problem_id' => $problem->id,
            'lang' => 'python',
            'function_name' => 'isPalindrome',
            'params' => [
                ['name' => 's', 'type' => 'str'],
            ],
            'returns' => ['type' => 'bool'],
        ]);

        ProblemTest::create([
            'problem_id' => $problem->id,
            'input' => ['A man, a plan, a canal: Panama'],
            'expected' => true,
            'is_edge' => false,
            'weight' => 1,
        ]);

        ProblemTest::create([
            'problem_id' => $problem->id,
            'input' => ['race a car'],
            'expected' => false,
            'is_edge' => false,
            'weight' => 1,
        ]);

        ProblemTest::create([
            'problem_id' => $problem->id,
            'input' => [' '],
            'expected' => true,
            'is_edge' => true,
            'weight' => 1,
        ]);

        ProblemTest::create([
            'problem_id' => $problem->id,
            'input' => ['0P'],
            'expected' => false,
            'is_edge' => true,
            'weight' => 2,
        ]);
    }

    protected function seedMergeTwoSortedLists(): void
    {
        $problem = Problem::create([
            'title' => 'Merge Two Sorted Lists',
            'difficulty' => 'Medium',
            'tags' => ['linked-list', 'recursion'],
            'constraints' => [
                'The number of nodes in both lists is in range [0, 50]',
                '-100 ≤ Node.val ≤ 100',
                'Both list1 and list2 are sorted in non-decreasing order',
            ],
            'description_md' => "## Problem\n\nYou are given the heads of two sorted linked lists `list1` and `list2`.\n\nMerge the two lists into one sorted list. The list should be made by splicing together the nodes of the first two lists.\n\nReturn the head of the merged linked list.\n\n## Examples\n\n**Example 1:**\n```\nInput: list1 = [1,2,4], list2 = [1,3,4]\nOutput: [1,1,2,3,4,4]\n```\n\n**Example 2:**\n```\nInput: list1 = [], list2 = []\nOutput: []\n```\n\n**Example 3:**\n```\nInput: list1 = [], list2 = [0]\nOutput: [0]\n```\n\n## Note\nFor testing purposes, linked lists are represented as arrays.",
            'is_premium' => false,
        ]);

        ProblemSignature::create([
            'problem_id' => $problem->id,
            'lang' => 'javascript',
            'function_name' => 'mergeTwoLists',
            'params' => [
                ['name' => 'list1', 'type' => 'number[]'],
                ['name' => 'list2', 'type' => 'number[]'],
            ],
            'returns' => ['type' => 'number[]'],
        ]);

        ProblemSignature::create([
            'problem_id' => $problem->id,
            'lang' => 'python',
            'function_name' => 'mergeTwoLists',
            'params' => [
                ['name' => 'list1', 'type' => 'List[int]'],
                ['name' => 'list2', 'type' => 'List[int]'],
            ],
            'returns' => ['type' => 'List[int]'],
        ]);

        ProblemTest::create([
            'problem_id' => $problem->id,
            'input' => [[1, 2, 4], [1, 3, 4]],
            'expected' => [1, 1, 2, 3, 4, 4],
            'is_edge' => false,
            'weight' => 1,
        ]);

        ProblemTest::create([
            'problem_id' => $problem->id,
            'input' => [[], []],
            'expected' => [],
            'is_edge' => true,
            'weight' => 1,
        ]);

        ProblemTest::create([
            'problem_id' => $problem->id,
            'input' => [[], [0]],
            'expected' => [0],
            'is_edge' => true,
            'weight' => 1,
        ]);

        ProblemTest::create([
            'problem_id' => $problem->id,
            'input' => [[5], [1, 2, 3, 4]],
            'expected' => [1, 2, 3, 4, 5],
            'is_edge' => false,
            'weight' => 2,
        ]);
    }

    protected function seedContainsDuplicate(): void
    {
        $problem = Problem::create([
            'title' => 'Contains Duplicate',
            'difficulty' => 'Easy',
            'tags' => ['array', 'hash-table', 'sorting'],
            'constraints' => [
                '1 ≤ nums.length ≤ 10⁵',
                '-10⁹ ≤ nums[i] ≤ 10⁹',
            ],
            'description_md' => "## Problem\n\nGiven an integer array `nums`, return `true` if any value appears **at least twice** in the array, and return `false` if every element is distinct.\n\n## Examples\n\n**Example 1:**\n```\nInput: nums = [1,2,3,1]\nOutput: true\nExplanation: The element 1 occurs at indices 0 and 3.\n```\n\n**Example 2:**\n```\nInput: nums = [1,2,3,4]\nOutput: false\nExplanation: All elements are distinct.\n```\n\n**Example 3:**\n```\nInput: nums = [1,1,1,3,3,4,3,2,4,2]\nOutput: true\n```",
            'is_premium' => false,
        ]);

        ProblemSignature::create([
            'problem_id' => $problem->id,
            'lang' => 'javascript',
            'function_name' => 'containsDuplicate',
            'params' => [
                ['name' => 'nums', 'type' => 'number[]'],
            ],
            'returns' => ['type' => 'boolean'],
        ]);

        ProblemSignature::create([
            'problem_id' => $problem->id,
            'lang' => 'python',
            'function_name' => 'containsDuplicate',
            'params' => [
                ['name' => 'nums', 'type' => 'List[int]'],
            ],
            'returns' => ['type' => 'bool'],
        ]);

        ProblemTest::create([
            'problem_id' => $problem->id,
            'input' => [[1, 2, 3, 1]],
            'expected' => true,
            'is_edge' => false,
            'weight' => 1,
        ]);

        ProblemTest::create([
            'problem_id' => $problem->id,
            'input' => [[1, 2, 3, 4]],
            'expected' => false,
            'is_edge' => false,
            'weight' => 1,
        ]);

        ProblemTest::create([
            'problem_id' => $problem->id,
            'input' => [[1, 1, 1, 3, 3, 4, 3, 2, 4, 2]],
            'expected' => true,
            'is_edge' => false,
            'weight' => 1,
        ]);

        ProblemTest::create([
            'problem_id' => $problem->id,
            'input' => [[1]],
            'expected' => false,
            'is_edge' => true,
            'weight' => 2,
        ]);
    }

    protected function seedLongestSubstringWithoutRepeating(): void
    {
        $problem = Problem::create([
            'title' => 'Longest Substring Without Repeating Characters',
            'difficulty' => 'Medium',
            'tags' => ['string', 'hash-table', 'sliding-window'],
            'constraints' => [
                '0 ≤ s.length ≤ 5 × 10⁴',
                's consists of English letters, digits, symbols and spaces',
            ],
            'description_md' => "## Problem\n\nGiven a string `s`, find the length of the **longest substring** without repeating characters.\n\nA **substring** is a contiguous non-empty sequence of characters within a string.\n\n## Examples\n\n**Example 1:**\n```\nInput: s = \"abcabcbb\"\nOutput: 3\nExplanation: The answer is \"abc\", with the length of 3.\n```\n\n**Example 2:**\n```\nInput: s = \"bbbbb\"\nOutput: 1\nExplanation: The answer is \"b\", with the length of 1.\n```\n\n**Example 3:**\n```\nInput: s = \"pwwkew\"\nOutput: 3\nExplanation: The answer is \"wke\", with the length of 3.\nNotice that the answer must be a substring, \"pwke\" is a subsequence and not a substring.\n```\n\n**Example 4:**\n```\nInput: s = \"\"\nOutput: 0\n```",
            'is_premium' => false,
        ]);

        ProblemSignature::create([
            'problem_id' => $problem->id,
            'lang' => 'javascript',
            'function_name' => 'lengthOfLongestSubstring',
            'params' => [
                ['name' => 's', 'type' => 'string'],
            ],
            'returns' => ['type' => 'number'],
        ]);

        ProblemSignature::create([
            'problem_id' => $problem->id,
            'lang' => 'python',
            'function_name' => 'lengthOfLongestSubstring',
            'params' => [
                ['name' => 's', 'type' => 'str'],
            ],
            'returns' => ['type' => 'int'],
        ]);

        ProblemTest::create([
            'problem_id' => $problem->id,
            'input' => ['abcabcbb'],
            'expected' => 3,
            'is_edge' => false,
            'weight' => 1,
        ]);

        ProblemTest::create([
            'problem_id' => $problem->id,
            'input' => ['bbbbb'],
            'expected' => 1,
            'is_edge' => false,
            'weight' => 1,
        ]);

        ProblemTest::create([
            'problem_id' => $problem->id,
            'input' => ['pwwkew'],
            'expected' => 3,
            'is_edge' => false,
            'weight' => 1,
        ]);

        ProblemTest::create([
            'problem_id' => $problem->id,
            'input' => [''],
            'expected' => 0,
            'is_edge' => true,
            'weight' => 1,
        ]);

        ProblemTest::create([
            'problem_id' => $problem->id,
            'input' => ['dvdf'],
            'expected' => 3,
            'is_edge' => true,
            'weight' => 2,
        ]);
    }
}
