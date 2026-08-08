from __future__ import annotations

from pathlib import Path

import pytest

from src.core.templates import TemplateLoader
from src.schemas.critique import (
    CoachConstraints,
    CritiquePayload,
    Difficulty,
    ProblemContext,
    Stage,
    Submission,
)
from src.services.prompt_builder_service import PromptBuilderService

PROMPTS_DIR = Path(__file__).resolve().parents[1] / "src" / "prompts"

STAGE_RUBRICS: dict[Stage, list[dict[str, int | str]]] = {
    Stage.CLARIFY: [
        {
            "key": "inputs_outputs",
            "max_score": 3,
            "expectation": "User should identify the inputs and the expected return/output contract.",
        },
        {
            "key": "constraints",
            "max_score": 3,
            "expectation": "User should mention relevant constraints or rules.",
        },
        {
            "key": "examples",
            "max_score": 6,
            "expectation": "User should provide at least two valid examples, including one edge case.",
        },
    ],
    Stage.APPROACH: [
        {
            "key": "strategy",
            "max_score": 2,
            "expectation": "User should state a clear high-level algorithmic idea.",
        },
        {
            "key": "justification",
            "max_score": 2,
            "expectation": "User should explain why the approach solves the problem.",
        },
        {
            "key": "complexity",
            "max_score": 2,
            "expectation": "User should state rough time and space complexity.",
        },
    ],
    Stage.PSEUDOCODE: [
        {
            "key": "step_order",
            "max_score": 3,
            "expectation": "User should present steps in a logical order.",
        },
        {
            "key": "bounds",
            "max_score": 3,
            "expectation": "User should make loop/index bounds and termination clear.",
        },
        {
            "key": "edge_handling",
            "max_score": 3,
            "expectation": "User should handle edge cases explicitly.",
        },
    ],
    Stage.BRUTE_FORCE: [
        {
            "key": "compiles",
            "max_score": 3,
            "expectation": "User code should compile and run.",
        },
        {
            "key": "signature",
            "max_score": 3,
            "expectation": "User code should use the correct function signature.",
        },
        {
            "key": "correctness",
            "max_score": 3,
            "expectation": "User should implement a logically correct naive solution.",
        },
    ],
    Stage.OPTIMIZE: [
        {
            "key": "optimization",
            "max_score": 2,
            "expectation": "User should implement a better algorithm than the brute-force solution.",
        },
        {
            "key": "complexity_target",
            "max_score": 1,
            "expectation": "User should achieve an improved Big-O complexity.",
        },
        {
            "key": "technique",
            "max_score": 1,
            "expectation": "User should explain the optimization technique used.",
        },
        {
            "key": "tradeoffs",
            "max_score": 2,
            "expectation": "User should articulate time/space tradeoffs.",
        },
    ],
}

STAGE_MARKERS: dict[Stage, str] = {
    Stage.CLARIFY: "# CLARIFY Stage Prompt",
    Stage.APPROACH: "# APPROACH Stage Prompt",
    Stage.PSEUDOCODE: "# PSEUDOCODE Stage Prompt",
    Stage.BRUTE_FORCE: "# BRUTE_FORCE Stage Prompt",
    Stage.OPTIMIZE: "# OPTIMIZE Stage Prompt",
}


def _make_payload(stage: Stage, **overrides) -> CritiquePayload:
    data = {
        "session_id": "session-1",
        "stage": stage,
        "rubric": STAGE_RUBRICS[stage],
        "problem_context": ProblemContext(
            title="Two Sum",
            description="Find two numbers that add up to target.",
            tags=["array", "hash-map"],
            constraints=["1 <= nums.length <= 10^4"],
            difficulty=Difficulty.EASY,
            signature={
                "function_name": "twoSum",
                "params": [{"name": "nums", "type": "int[]"}, {"name": "target", "type": "int"}],
                "returns": {"type": "int[]"},
            },
        ),
        "submission": Submission(
            inputs_outputs="nums: int[], target: int -> int[]",
            constraints=["nums length at least 2"],
            examples="[2,7,11,15], 9 -> [0,1]",
        ),
        "auto_signals": {"missing_fields": []},
        "coach_constraints": CoachConstraints(
            no_code=True,
            no_solution_reveal=True,
            feedback_style="socratic",
            max_questions=2,
            max_tokens=800,
        ),
    }
    data.update(overrides)
    return CritiquePayload(**data)


@pytest.fixture
def builder() -> PromptBuilderService:
    return PromptBuilderService(templates=TemplateLoader(PROMPTS_DIR))


@pytest.mark.parametrize("stage", list(STAGE_RUBRICS))
def test_build_includes_stage_prompt_and_shared_sections(
    builder: PromptBuilderService,
    stage: Stage,
) -> None:
    stage_template = (PROMPTS_DIR / "stages" / f"{stage.value.lower()}.md").read_text(
        encoding="utf-8"
    ).strip()
    system_template = (PROMPTS_DIR / "system.md").read_text(encoding="utf-8").strip()
    payload = _make_payload(stage)

    messages = builder.build(payload)

    assert len(messages) == 2
    assert messages[0].role == "system"
    assert messages[0].content == system_template
    assert messages[1].role == "user"

    user_prompt = messages[1].content
    assert user_prompt.startswith(f"Stage: {stage.value}")
    assert STAGE_MARKERS[stage] in user_prompt
    assert stage_template in user_prompt
    assert "Problem context:" in user_prompt
    assert "Title: Two Sum" in user_prompt
    assert "Rubric:" in user_prompt
    assert "User submission:" in user_prompt
    assert "Auto signals (evidence only, not automatic scores):" in user_prompt
    assert "Coach constraints:" in user_prompt
    assert "Return valid JSON only" in user_prompt
    assert "The submitted code is learner-authored" in user_prompt

    for item in STAGE_RUBRICS[stage]:
        assert f"- {item['key']} (max {item['max_score']}): {item['expectation']}" in user_prompt
        assert f'"{item["key"]}": {{ "score": <integer 0-{item["max_score"]}>' in user_prompt


@pytest.mark.parametrize("stage", list(STAGE_RUBRICS))
def test_build_stage_prompt_is_not_mixed_with_other_stages(
    builder: PromptBuilderService,
    stage: Stage,
) -> None:
    user_prompt = builder.build(_make_payload(stage))[1].content

    for other_stage, marker in STAGE_MARKERS.items():
        if other_stage is stage:
            assert marker in user_prompt
        else:
            assert marker not in user_prompt


def test_build_omits_auto_signals_section_when_empty(builder: PromptBuilderService) -> None:
    payload = _make_payload(Stage.CLARIFY, auto_signals={})

    user_prompt = builder.build(payload)[1].content

    assert "Auto signals (evidence only, not automatic scores):" not in user_prompt


def test_build_rejects_unsupported_done_stage(builder: PromptBuilderService) -> None:
    payload = _make_payload(Stage.CLARIFY).model_copy(update={"stage": Stage.DONE})

    with pytest.raises(ValueError, match="Stage DONE is not supported"):
        builder.build(payload)


def test_build_rejects_empty_rubric(builder: PromptBuilderService) -> None:
    payload = _make_payload(Stage.CLARIFY, rubric=[])

    with pytest.raises(ValueError, match="Rubric criteria are required"):
        builder.build(payload)


def test_build_rejects_missing_stage_template(tmp_path: Path) -> None:
    templates = TemplateLoader(tmp_path)
    (tmp_path / "system.md").write_text("system prompt", encoding="utf-8")
    builder = PromptBuilderService(templates=templates)
    payload = _make_payload(Stage.CLARIFY)

    with pytest.raises(ValueError, match="Missing prompt template for stage: CLARIFY"):
        builder.build(payload)
