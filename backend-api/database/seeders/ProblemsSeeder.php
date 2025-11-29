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
        // $this->seedTwoSum();
        // $this->seedValidPalindrome();
        // $this->seedMergeTwoSortedLists();
        // $this->seedContainsDuplicate();
        // $this->seedLongestSubstringWithoutRepeating();
        // $this->seedMoveZeroes();
        // $this->seedReverseLinkedList();
        $this->seedThreeSum();
        $this->seedTrappingRainWater();
        $this->seedMedianOfTwoSortedArrays();
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

    protected function seedMoveZeroes(): void
    {
        $problem = Problem::create([
            'title' => 'Move Zeroes',
            'difficulty' => 'Easy',
            'tags' => ['array', 'two-pointers'],
            'constraints' => [
                '1 ≤ nums.length ≤ 10⁴',
                '-2³¹ ≤ nums[i] ≤ 2³¹ - 1',
            ],
            'description_md' => "## Problem\n\nGiven an integer array `nums`, move all `0`'s to the end of it while maintaining the relative order of the non-zero elements.\n\n**Note:** You must do this in-place without making a copy of the array.\n\n## Examples\n\n**Example 1:**\n```\nInput: nums = [0,1,0,3,12]\nOutput: [1,3,12,0,0]\n```\n\n**Example 2:**\n```\nInput: nums = [0]\nOutput: [0]\n```\n\n**Example 3:**\n```\nInput: nums = [1,2,3]\nOutput: [1,2,3]\nExplanation: No zeros to move.\n```",
            'is_premium' => false,
        ]);

        ProblemSignature::create([
            'problem_id' => $problem->id,
            'lang' => 'javascript',
            'function_name' => 'moveZeroes',
            'params' => [
                ['name' => 'nums', 'type' => 'number[]'],
            ],
            'returns' => ['type' => 'void'],
        ]);

        ProblemSignature::create([
            'problem_id' => $problem->id,
            'lang' => 'python',
            'function_name' => 'moveZeroes',
            'params' => [
                ['name' => 'nums', 'type' => 'List[int]'],
            ],
            'returns' => ['type' => 'None'],
        ]);

        ProblemTest::create([
            'problem_id' => $problem->id,
            'input' => [[0, 1, 0, 3, 12]],
            'expected' => [1, 3, 12, 0, 0],
            'is_edge' => false,
            'weight' => 1,
        ]);

        ProblemTest::create([
            'problem_id' => $problem->id,
            'input' => [[0]],
            'expected' => [0],
            'is_edge' => true,
            'weight' => 1,
        ]);

        ProblemTest::create([
            'problem_id' => $problem->id,
            'input' => [[1, 2, 3]],
            'expected' => [1, 2, 3],
            'is_edge' => false,
            'weight' => 1,
        ]);

        ProblemTest::create([
            'problem_id' => $problem->id,
            'input' => [[0, 0, 1]],
            'expected' => [1, 0, 0],
            'is_edge' => true,
            'weight' => 2,
        ]);
    }

    protected function seedReverseLinkedList(): void
    {
        $problem = Problem::create([
            'title' => 'Reverse Linked List',
            'difficulty' => 'Easy',
            'tags' => ['linked-list', 'recursion'],
            'constraints' => [
                'The number of nodes in the list is the range [0, 5000]',
                '-5000 ≤ Node.val ≤ 5000',
            ],
            'description_md' => "## Problem\n\nGiven the `head` of a singly linked list, reverse the list, and return the reversed list.\n\n## Examples\n\n**Example 1:**\n```\nInput: head = [1,2,3,4,5]\nOutput: [5,4,3,2,1]\n```\n\n**Example 2:**\n```\nInput: head = [1,2]\nOutput: [2,1]\n```\n\n**Example 3:**\n```\nInput: head = []\nOutput: []\n```\n\n## Note\nFor testing purposes, linked lists are represented as arrays.\n\n## Follow-up\nCan you solve it both iteratively and recursively?",
            'is_premium' => false,
        ]);

        ProblemSignature::create([
            'problem_id' => $problem->id,
            'lang' => 'javascript',
            'function_name' => 'reverseList',
            'params' => [
                ['name' => 'head', 'type' => 'number[]'],
            ],
            'returns' => ['type' => 'number[]'],
        ]);

        ProblemSignature::create([
            'problem_id' => $problem->id,
            'lang' => 'python',
            'function_name' => 'reverseList',
            'params' => [
                ['name' => 'head', 'type' => 'List[int]'],
            ],
            'returns' => ['type' => 'List[int]'],
        ]);

        ProblemTest::create([
            'problem_id' => $problem->id,
            'input' => [[1, 2, 3, 4, 5]],
            'expected' => [5, 4, 3, 2, 1],
            'is_edge' => false,
            'weight' => 1,
        ]);

        ProblemTest::create([
            'problem_id' => $problem->id,
            'input' => [[1, 2]],
            'expected' => [2, 1],
            'is_edge' => false,
            'weight' => 1,
        ]);

        ProblemTest::create([
            'problem_id' => $problem->id,
            'input' => [[]],
            'expected' => [],
            'is_edge' => true,
            'weight' => 1,
        ]);

        ProblemTest::create([
            'problem_id' => $problem->id,
            'input' => [[1]],
            'expected' => [1],
            'is_edge' => true,
            'weight' => 2,
        ]);
    }

    protected function seedThreeSum(): void
    {
        $problem = Problem::create([
            'title' => '3Sum',
            'difficulty' => 'Medium',
            'tags' => ['array', 'two-pointers', 'sorting'],
            'constraints' => [
                '3 ≤ nums.length ≤ 3000',
                '-10⁵ ≤ nums[i] ≤ 10⁵',
            ],
            'description_md' => "## Problem\n\nGiven an integer array `nums`, return all the triplets `[nums[i], nums[j], nums[k]]` such that `i != j`, `i != k`, and `j != k`, and `nums[i] + nums[j] + nums[k] == 0`.\n\nNotice that the solution set must not contain duplicate triplets.\n\n## Examples\n\n**Example 1:**\n```\nInput: nums = [-1,0,1,2,-1,-4]\nOutput: [[-1,-1,2],[-1,0,1]]\nExplanation:\nnums[0] + nums[1] + nums[2] = (-1) + 0 + 1 = 0.\nnums[1] + nums[2] + nums[4] = 0 + 1 + (-1) = 0.\nnums[0] + nums[3] + nums[4] = (-1) + 2 + (-1) = 0.\nThe distinct triplets are [-1,0,1] and [-1,-1,2].\n```\n\n**Example 2:**\n```\nInput: nums = [0,1,1]\nOutput: []\nExplanation: The only possible triplet does not sum up to 0.\n```\n\n**Example 3:**\n```\nInput: nums = [0,0,0]\nOutput: [[0,0,0]]\nExplanation: The only possible triplet sums up to 0.\n```",
            'is_premium' => false,
        ]);

        ProblemSignature::create([
            'problem_id' => $problem->id,
            'lang' => 'javascript',
            'function_name' => 'threeSum',
            'params' => [
                ['name' => 'nums', 'type' => 'number[]'],
            ],
            'returns' => ['type' => 'number[][]'],
        ]);

        ProblemSignature::create([
            'problem_id' => $problem->id,
            'lang' => 'python',
            'function_name' => 'threeSum',
            'params' => [
                ['name' => 'nums', 'type' => 'List[int]'],
            ],
            'returns' => ['type' => 'List[List[int]]'],
        ]);

        ProblemTest::create([
            'problem_id' => $problem->id,
            'input' => [[-1, 0, 1, 2, -1, -4]],
            'expected' => [[-1, -1, 2], [-1, 0, 1]],
            'is_edge' => false,
            'weight' => 1,
        ]);

        ProblemTest::create([
            'problem_id' => $problem->id,
            'input' => [[0, 1, 1]],
            'expected' => [],
            'is_edge' => false,
            'weight' => 1,
        ]);

        ProblemTest::create([
            'problem_id' => $problem->id,
            'input' => [[0, 0, 0]],
            'expected' => [[0, 0, 0]],
            'is_edge' => true,
            'weight' => 1,
        ]);

        ProblemTest::create([
            'problem_id' => $problem->id,
            'input' => [[-2, 0, 1, 1, 2]],
            'expected' => [[-2, 0, 2], [-2, 1, 1]],
            'is_edge' => false,
            'weight' => 2,
        ]);
    }

    protected function seedTrappingRainWater(): void
    {
        $problem = Problem::create([
            'title' => 'Trapping Rain Water',
            'difficulty' => 'Hard',
            'tags' => ['array', 'two-pointers', 'dynamic-programming', 'stack'],
            'constraints' => [
                'n == height.length',
                '1 ≤ n ≤ 2 × 10⁴',
                '0 ≤ height[i] ≤ 10⁵',
            ],
            'description_md' => "## Problem\n\nGiven `n` non-negative integers representing an elevation map where the width of each bar is `1`, compute how much water it can trap after raining.\n\n## Examples\n\n**Example 1:**\n```\nInput: height = [0,1,0,2,1,0,1,3,2,1,2,1]\nOutput: 6\nExplanation: The elevation map (black section) is represented by array [0,1,0,2,1,0,1,3,2,1,2,1]. In this case, 6 units of rain water (blue section) are being trapped.\n```\n\n**Example 2:**\n```\nInput: height = [4,2,0,3,2,5]\nOutput: 9\n```\n\n**Example 3:**\n```\nInput: height = []\nOutput: 0\n```",
            'is_premium' => false,
        ]);

        ProblemSignature::create([
            'problem_id' => $problem->id,
            'lang' => 'javascript',
            'function_name' => 'trap',
            'params' => [
                ['name' => 'height', 'type' => 'number[]'],
            ],
            'returns' => ['type' => 'number'],
        ]);

        ProblemSignature::create([
            'problem_id' => $problem->id,
            'lang' => 'python',
            'function_name' => 'trap',
            'params' => [
                ['name' => 'height', 'type' => 'List[int]'],
            ],
            'returns' => ['type' => 'int'],
        ]);

        ProblemTest::create([
            'problem_id' => $problem->id,
            'input' => [[0, 1, 0, 2, 1, 0, 1, 3, 2, 1, 2, 1]],
            'expected' => 6,
            'is_edge' => false,
            'weight' => 1,
        ]);

        ProblemTest::create([
            'problem_id' => $problem->id,
            'input' => [[4, 2, 0, 3, 2, 5]],
            'expected' => 9,
            'is_edge' => false,
            'weight' => 1,
        ]);

        ProblemTest::create([
            'problem_id' => $problem->id,
            'input' => [[]],
            'expected' => 0,
            'is_edge' => true,
            'weight' => 1,
        ]);

        ProblemTest::create([
            'problem_id' => $problem->id,
            'input' => [[3, 0, 2, 0, 4]],
            'expected' => 7,
            'is_edge' => false,
            'weight' => 2,
        ]);
    }

    protected function seedMedianOfTwoSortedArrays(): void
    {
        $problem = Problem::create([
            'title' => 'Median of Two Sorted Arrays',
            'difficulty' => 'Hard',
            'tags' => ['array', 'binary-search', 'divide-and-conquer'],
            'constraints' => [
                'nums1.length == m',
                'nums2.length == n',
                '0 ≤ m ≤ 1000',
                '0 ≤ n ≤ 1000',
                '1 ≤ m + n ≤ 2000',
                '-10⁶ ≤ nums1[i], nums2[i] ≤ 10⁶',
            ],
            'description_md' => "## Problem\n\nGiven two sorted arrays `nums1` and `nums2` of size `m` and `n` respectively, return **the median** of the two sorted arrays.\n\nThe overall run time complexity should be `O(log (m+n))`.\n\n## Examples\n\n**Example 1:**\n```\nInput: nums1 = [1,3], nums2 = [2]\nOutput: 2.00000\nExplanation: merged array = [1,2,3] and median is 2.\n```\n\n**Example 2:**\n```\nInput: nums1 = [1,2], nums2 = [3,4]\nOutput: 2.50000\nExplanation: merged array = [1,2,3,4] and median is (2 + 3) / 2 = 2.5.\n```\n\n**Example 3:**\n```\nInput: nums1 = [], nums2 = [1]\nOutput: 1.00000\n```",
            'is_premium' => false,
        ]);

        ProblemSignature::create([
            'problem_id' => $problem->id,
            'lang' => 'javascript',
            'function_name' => 'findMedianSortedArrays',
            'params' => [
                ['name' => 'nums1', 'type' => 'number[]'],
                ['name' => 'nums2', 'type' => 'number[]'],
            ],
            'returns' => ['type' => 'number'],
        ]);

        ProblemSignature::create([
            'problem_id' => $problem->id,
            'lang' => 'python',
            'function_name' => 'findMedianSortedArrays',
            'params' => [
                ['name' => 'nums1', 'type' => 'List[int]'],
                ['name' => 'nums2', 'type' => 'List[int]'],
            ],
            'returns' => ['type' => 'float'],
        ]);

        ProblemTest::create([
            'problem_id' => $problem->id,
            'input' => [[1, 3], [2]],
            'expected' => 2.0,
            'is_edge' => false,
            'weight' => 1,
        ]);

        ProblemTest::create([
            'problem_id' => $problem->id,
            'input' => [[1, 2], [3, 4]],
            'expected' => 2.5,
            'is_edge' => false,
            'weight' => 1,
        ]);

        ProblemTest::create([
            'problem_id' => $problem->id,
            'input' => [[], [1]],
            'expected' => 1.0,
            'is_edge' => true,
            'weight' => 1,
        ]);

        ProblemTest::create([
            'problem_id' => $problem->id,
            'input' => [[2], []],
            'expected' => 2.0,
            'is_edge' => true,
            'weight' => 2,
        ]);
    }
}
