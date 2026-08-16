from __future__ import annotations

from typing import Any

from src.schemas.critique import Stage

_PROBLEM_CONTEXT = {
    "title": "Two Sum",
    "description": (
        "Given an array of integers, return indices of the two numbers "
        "such that they add up to a specific target."
    ),
    "tags": ["array", "hash table"],
    "constraints": ["O(n)", "O(1)"],
    "difficulty": "Easy",
    "signature": {
        "function_name": "twoSum",
        "params": [
            {"name": "nums", "type": "int[]"},
            {"name": "target", "type": "int"},
        ],
        "returns": {"type": "int[]"},
    },
}

_COACH_CONSTRAINTS = {
    "no_code": True,
    "no_solution_reveal": True,
    "feedback_style": "socratic",
    "max_questions": 2,
    "max_tokens": 500,
}


def _example(
    stage: Stage,
    *,
    summary: str,
    rubric: list[dict[str, Any]],
    submission: dict[str, Any],
    auto_signals: dict[str, Any] | None = None,
) -> dict[str, Any]:
    return {
        "summary": summary,
        "value": {
            "session_id": "123",
            "stage": stage,
            "rubric": rubric,
            "problem_context": _PROBLEM_CONTEXT,
            "submission": submission,
            "auto_signals": auto_signals or {},
            "coach_constraints": _COACH_CONSTRAINTS,
        },
    }


CRITIQUE_EXAMPLES = [
    _example(
        Stage.CLARIFY,
        summary="Clarify stage critique",
        rubric=[
            {"key": "inputs_outputs", "max_score": 3, "expectation": "Clear I/O contract"},
            {"key": "constraints", "max_score": 3, "expectation": "Relevant constraints"},
            {"key": "examples", "max_score": 6, "expectation": "Two examples including an edge"},
        ],
        submission={
            "inputs_outputs": "Input: nums and target. Output: indices of the pair.",
            "constraints": "Exactly one solution. Do not reuse the same element.",
            "examples": "Example 1: [2,7,11,15], 9 -> [0,1]\nExample 2 (edge): [3,3], 6 -> [0,1]",
        },
        auto_signals={
            "mentioned_param_names": ["nums", "target"],
            "missing_param_names": [],
            "example_count": 2,
            "has_marked_edge_case": True,
        },
    ),
    _example(
        Stage.APPROACH,
        summary="Approach stage critique",
        rubric=[
            {"key": "strategy", "max_score": 2, "expectation": "Clear high-level idea"},
            {"key": "justification", "max_score": 2, "expectation": "Why it works"},
            {"key": "complexity", "max_score": 2, "expectation": "Rough time and space"},
        ],
        submission={
            "strategy": "Scan once and store seen values in a hash map.",
            "justification": "The complement is recoverable from values already seen.",
            "complexity": "Time O(n), space O(n).",
        },
    ),
    _example(
        Stage.PSEUDOCODE,
        summary="Pseudocode stage critique",
        rubric=[
            {"key": "step_order", "max_score": 3, "expectation": "Logical step order"},
            {"key": "bounds", "max_score": 3, "expectation": "Clear loop bounds"},
            {"key": "edge_handling", "max_score": 3, "expectation": "Edges handled"},
        ],
        submission={
            "steps_text": "Walk the array, store seen values, return when the complement exists.",
        },
    ),
    _example(
        Stage.BRUTE_FORCE,
        summary="Brute force stage critique",
        rubric=[
            {"key": "compiles", "max_score": 3, "expectation": "Code runs"},
            {"key": "signature", "max_score": 3, "expectation": "Correct signature"},
            {"key": "correctness", "max_score": 3, "expectation": "Naive solution is logically correct"},
        ],
        submission={
            "code": "def twoSum(nums, target):\n    for i in range(len(nums)):\n        for j in range(i + 1, len(nums)):\n            if nums[i] + nums[j] == target:\n                return [i, j]",
            "lang": "python",
        },
    ),
    _example(
        Stage.OPTIMIZE,
        summary="Optimize stage critique",
        rubric=[
            {"key": "optimization", "max_score": 2, "expectation": "Better algorithm"},
            {"key": "complexity_target", "max_score": 1, "expectation": "Improved Big-O"},
            {"key": "technique", "max_score": 1, "expectation": "Named technique"},
            {"key": "tradeoffs", "max_score": 2, "expectation": "Time/space tradeoffs"},
        ],
        submission={
            "code": "def twoSum(nums, target):\n    seen = {}\n    for i, n in enumerate(nums):\n        if target - n in seen:\n            return [seen[target - n], i]\n        seen[n] = i",
            "lang": "python",
            "complexity_analysis": "Time O(n), space O(n).",
            "optimization_technique": "Hash map lookup",
            "tradeoffs": "Extra memory to avoid a nested scan.",
        },
    ),
]
