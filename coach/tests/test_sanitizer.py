from __future__ import annotations

import pytest

from src.schemas.critique import CritiqueResponse, ScoreDetail, Stage
from src.services.sanitizer import FALLBACK_QUESTION, STAGE_FALLBACKS, Sanitizer

LEAK_FLAGS = (
    "fenced_code_detected",
    "inline_code_removed",
    "syntactic_code_detected",
    "code_span_removed",
)


def _response(**overrides) -> CritiqueResponse:
    data = {
        "coach_msg": "Your inputs are clear. Add an edge-case example.",
        "scores": {
            "inputs_outputs": ScoreDetail(
                score=3,
                max_score=3,
                reason="Contract is clear",
            ),
        },
        "flags": {
            "too_vague": False,
            "code_leak_blocked": False,
            "prompt_injection_detected": False,
            "invalid_json": False,
            "fallback_used": False,
            "missing_scores": False,
        },
        "questions": ["What happens with an empty array?"],
    }
    data.update(overrides)
    return CritiqueResponse(**data)


def _assert_leak_flags(
    result: CritiqueResponse,
    *,
    blocked: bool = False,
    fenced: bool = False,
    inline: bool = False,
    syntactic: bool = False,
    span: bool = False,
) -> None:
    assert result.flags["code_leak_blocked"] is blocked
    assert result.flags["fenced_code_detected"] is fenced
    assert result.flags["inline_code_removed"] is inline
    assert result.flags["syntactic_code_detected"] is syntactic
    assert result.flags["code_span_removed"] is span


@pytest.fixture
def sanitizer() -> Sanitizer:
    return Sanitizer()


def test_leaves_prose_unchanged(sanitizer: Sanitizer) -> None:
    result = sanitizer.sanitize(_response(), Stage.CLARIFY)

    assert result.coach_msg == "Your inputs are clear. Add an edge-case example."
    assert result.questions == ["What happens with an empty array?"]
    assert result.scores["inputs_outputs"].reason == "Contract is clear"
    _assert_leak_flags(result)


def test_keeps_complexity_and_short_identifiers(sanitizer: Sanitizer) -> None:
    result = sanitizer.sanitize(
        _response(
            coach_msg=(
                "A hash map can get you to `O(n)` time. "
                "What does `nums` represent here?"
            ),
            questions=["What happens when n = 0?"],
        ),
        Stage.CLARIFY,
    )

    assert "`O(n)`" in result.coach_msg
    assert "`nums`" in result.coach_msg
    assert result.questions == ["What happens when n = 0?"]
    _assert_leak_flags(result)


def test_strips_fenced_python_block(sanitizer: Sanitizer) -> None:
    result = sanitizer.sanitize(
        _response(
            coach_msg=(
                "Your approach is close.\n"
                "```python\n"
                "def two_sum(nums, target):\n"
                "    seen = {}\n"
                "    for i, n in enumerate(nums):\n"
                "        if target - n in seen:\n"
                "            return [seen[target - n], i]\n"
                "```\n"
                "What happens on duplicates?"
            )
        ),
        Stage.CLARIFY,
    )

    assert "def two_sum" not in result.coach_msg
    assert "seen = {}" not in result.coach_msg
    assert "Your approach is close." in result.coach_msg
    assert "What happens on duplicates?" in result.coach_msg
    _assert_leak_flags(result, blocked=True, fenced=True)


def test_strips_unclosed_fence_to_end(sanitizer: Sanitizer) -> None:
    result = sanitizer.sanitize(
        _response(
            coach_msg=(
                "Try this instead:\n"
                "```js\n"
                "function twoSum(nums, target) {\n"
                "  return nums;\n"
                "}"
            )
        ),
        Stage.CLARIFY,
    )

    assert "function twoSum" not in result.coach_msg
    assert "Try this instead:" in result.coach_msg
    _assert_leak_flags(result, blocked=True, fenced=True)


def test_strips_unfenced_function_body(sanitizer: Sanitizer) -> None:
    result = sanitizer.sanitize(
        _response(
            coach_msg=(
                "A working sketch:\n"
                "def two_sum(nums, target):\n"
                "    seen = {}\n"
                "    for i, n in enumerate(nums):\n"
                "        complement = target - n\n"
                "        if complement in seen:\n"
                "            return [seen[complement], i]\n"
                "        seen[n] = i"
            )
        ),
        Stage.CLARIFY,
    )

    assert "def two_sum" not in result.coach_msg
    assert "seen = {}" not in result.coach_msg
    _assert_leak_flags(result, blocked=True, syntactic=True, span=True)


def test_strips_javascript_const_and_arrow_function(sanitizer: Sanitizer) -> None:
    result = sanitizer.sanitize(
        _response(
            coach_msg=(
                "const twoSum = (nums, target) => {\n"
                "  const seen = {};\n"
                "  for (let i = 0; i < nums.length; i++) {\n"
                "    seen[nums[i]] = i;\n"
                "  }\n"
                "};"
            )
        ),
        Stage.APPROACH,
    )

    assert "const twoSum" not in result.coach_msg
    assert result.coach_msg == STAGE_FALLBACKS[Stage.APPROACH]
    _assert_leak_flags(result, blocked=True, syntactic=True, span=True)


def test_strips_inline_syntactic_backticks(sanitizer: Sanitizer) -> None:
    result = sanitizer.sanitize(
        _response(
            coach_msg=(
                "Don't write `def two_sum(nums, target):` — "
                "describe the steps in words."
            )
        ),
        Stage.CLARIFY,
    )

    assert "def two_sum" not in result.coach_msg
    assert "describe the steps in words." in result.coach_msg
    _assert_leak_flags(result, blocked=True, inline=True, syntactic=True)


def test_drops_questions_that_contain_code(sanitizer: Sanitizer) -> None:
    result = sanitizer.sanitize(
        _response(
            questions=[
                "What happens with an empty array?",
                "Could you fill in `def two_sum(nums, target):`?",
                "Why is O(n^2) too slow here?",
            ]
        ),
        Stage.CLARIFY,
    )

    assert result.questions == [
        "What happens with an empty array?",
        "Why is O(n^2) too slow here?",
    ]
    _assert_leak_flags(result, blocked=True, inline=True, syntactic=True)


def test_sanitizes_score_reasons(sanitizer: Sanitizer) -> None:
    result = sanitizer.sanitize(
        _response(
            scores={
                "inputs_outputs": ScoreDetail(
                    score=2,
                    max_score=3,
                    reason=(
                        "Missing the return contract; they should have written "
                        "def two_sum(nums, target): return [i, j]"
                    ),
                )
            }
        ),
        Stage.CLARIFY,
    )

    assert "def two_sum" not in (result.scores["inputs_outputs"].reason or "")
    _assert_leak_flags(result, blocked=True, syntactic=True, span=True)


def test_uses_stage_fallback_when_entire_message_is_code(
    sanitizer: Sanitizer,
) -> None:
    result = sanitizer.sanitize(
        _response(
            coach_msg=(
                "```python\n"
                "print('hello')\n"
                "```"
            )
        ),
        Stage.CLARIFY,
    )

    assert result.coach_msg == STAGE_FALLBACKS[Stage.CLARIFY]
    _assert_leak_flags(result, blocked=True, fenced=True)


def test_does_not_flag_english_with_common_keywords(sanitizer: Sanitizer) -> None:
    result = sanitizer.sanitize(
        _response(
            coach_msg=(
                "If the array is empty, return an empty result. "
                "For each value, ask whether its complement was already seen. "
                "That class of problems often needs a lookup."
            ),
            questions=["Can you return the indices instead of the values?"],
        ),
        Stage.CLARIFY,
    )

    assert "If the array is empty" in result.coach_msg
    assert result.questions == [
        "Can you return the indices instead of the values?"
    ]
    _assert_leak_flags(result)


def test_strips_indented_unfenced_block(sanitizer: Sanitizer) -> None:
    result = sanitizer.sanitize(
        _response(
            coach_msg=(
                "Your scan is on the right track:\n"
                "    seen = {}\n"
                "    seen[nums[i]] = i\n"
                "    return [seen[complement], i]\n"
                "Name the invariant instead of writing it."
            )
        ),
        Stage.PSEUDOCODE,
    )

    assert "seen = {}" not in result.coach_msg
    assert "Name the invariant instead of writing it." in result.coach_msg
    _assert_leak_flags(result, blocked=True, syntactic=True, span=True)


def test_strips_short_multiline_indented_snippet(sanitizer: Sanitizer) -> None:
    result = sanitizer.sanitize(
        _response(
            coach_msg=(
                "Don't do this:\n"
                "    seen[nums[i]] = i\n"
                "    seen[nums[j]] = j\n"
                "Explain the invariant instead."
            )
        ),
        Stage.CLARIFY,
    )

    assert "seen[nums[i]]" not in result.coach_msg
    assert "Explain the invariant instead." in result.coach_msg
    _assert_leak_flags(result, blocked=True, span=True)


def test_merges_leak_reasons_across_fields(sanitizer: Sanitizer) -> None:
    result = sanitizer.sanitize(
        _response(
            coach_msg=(
                "Close.\n"
                "```python\n"
                "print(1)\n"
                "```"
            ),
            questions=["Could you fill in `def two_sum(nums, target):`?"],
        ),
        Stage.CLARIFY,
    )

    _assert_leak_flags(
        result,
        blocked=True,
        fenced=True,
        inline=True,
        syntactic=True,
    )
    assert all(key in result.flags for key in LEAK_FLAGS)


def test_blocks_python_for_loop_over_list(sanitizer: Sanitizer) -> None:
    result = sanitizer.sanitize(
        _response(
            coach_msg=(
                "A working sketch:\n"
                "for num in nums:\n"
                "Ask what you would store on each pass instead."
            )
        ),
        Stage.APPROACH,
    )

    assert "for num in nums" not in result.coach_msg
    assert "Ask what you would store on each pass instead." in result.coach_msg
    _assert_leak_flags(result, blocked=True, syntactic=True, span=True)


def test_blocks_python_for_loop_with_tuple_unpack(sanitizer: Sanitizer) -> None:
    result = sanitizer.sanitize(
        _response(
            coach_msg="Don't write `for key, value in items:` — name the lookup instead."
        ),
        Stage.APPROACH,
    )

    assert "for key, value in items" not in result.coach_msg
    assert "name the lookup instead." in result.coach_msg
    _assert_leak_flags(result, blocked=True, inline=True, syntactic=True)


def test_strips_return_with_indexing(sanitizer: Sanitizer) -> None:
    result = sanitizer.sanitize(
        _response(
            coach_msg=(
                "Don't write this:\n"
                "    return seen[target - num]\n"
                "Ask what you look up instead."
            )
        ),
        Stage.CLARIFY,
    )

    assert "return seen" not in result.coach_msg
    assert "Ask what you look up instead." in result.coach_msg
    _assert_leak_flags(result, blocked=True, span=True)


def test_keeps_english_return_without_code_signals(sanitizer: Sanitizer) -> None:
    result = sanitizer.sanitize(
        _response(
            coach_msg="If the input is empty, return an empty result."
        ),
        Stage.CLARIFY,
    )

    assert "return an empty result" in result.coach_msg
    _assert_leak_flags(result)


def test_strips_short_inline_solution_snippet(sanitizer: Sanitizer) -> None:
    result = sanitizer.sanitize(
        _response(
            coach_msg=(
                "Don't use `seen[complement]` or `target - nums[i]`. "
                "Name the lookup in words."
            )
        ),
        Stage.APPROACH,
    )

    assert "seen[complement]" not in result.coach_msg
    assert "target - nums[i]" not in result.coach_msg
    assert "Name the lookup in words." in result.coach_msg
    _assert_leak_flags(result, blocked=True, inline=True)


def test_inserts_fallback_question_when_all_questions_blocked(
    sanitizer: Sanitizer,
) -> None:
    result = sanitizer.sanitize(
        _response(
            questions=[
                "Could you fill in `def two_sum(nums, target):`?",
                "What about `for num in nums:` next?",
            ]
        ),
        Stage.CLARIFY,
    )

    assert result.questions == [FALLBACK_QUESTION]
    _assert_leak_flags(result, blocked=True, inline=True, syntactic=True)


def test_blocks_php_arrow_function(sanitizer: Sanitizer) -> None:
    result = sanitizer.sanitize(
        _response(
            coach_msg=(
                "fn($nums, $target) => {\n"
                "    $seen = [];\n"
                "    return $seen;\n"
                "}"
            )
        ),
        Stage.APPROACH,
    )

    assert "fn($nums" not in result.coach_msg
    assert "$seen" not in result.coach_msg
    assert result.coach_msg == STAGE_FALLBACKS[Stage.APPROACH]
    _assert_leak_flags(result, blocked=True, syntactic=True, span=True)


def test_blocks_php_foreach_and_dollar_assignment(sanitizer: Sanitizer) -> None:
    result = sanitizer.sanitize(
        _response(
            coach_msg=(
                "A working sketch:\n"
                "foreach ($nums as $i => $num) {\n"
                "    $seen[$num] = $i;\n"
                "}\n"
                "Name the lookup instead of writing it."
            )
        ),
        Stage.APPROACH,
    )

    assert "foreach" not in result.coach_msg
    assert "$seen" not in result.coach_msg
    assert "Name the lookup instead of writing it." in result.coach_msg
    _assert_leak_flags(result, blocked=True, syntactic=True, span=True)


def test_strips_inline_php_arrow_syntax(sanitizer: Sanitizer) -> None:
    result = sanitizer.sanitize(
        _response(
            coach_msg=(
                "Don't write `fn($nums) => $nums[0]` — "
                "describe the lookup in words."
            )
        ),
        Stage.CLARIFY,
    )

    assert "fn($nums)" not in result.coach_msg
    assert "describe the lookup in words." in result.coach_msg
    _assert_leak_flags(result, blocked=True, inline=True, syntactic=True)


def test_strips_fenced_php_block(sanitizer: Sanitizer) -> None:
    result = sanitizer.sanitize(
        _response(
            coach_msg=(
                "Your approach is close.\n"
                "```php\n"
                "<?php\n"
                "function twoSum($nums, $target) {\n"
                "    $seen = [];\n"
                "    foreach ($nums as $i => $num) {\n"
                "        $seen[$num] = $i;\n"
                "    }\n"
                "}\n"
                "```\n"
                "What happens on duplicates?"
            )
        ),
        Stage.CLARIFY,
    )

    assert "function twoSum" not in result.coach_msg
    assert "foreach" not in result.coach_msg
    assert "Your approach is close." in result.coach_msg
    assert "What happens on duplicates?" in result.coach_msg
    _assert_leak_flags(result, blocked=True, fenced=True)