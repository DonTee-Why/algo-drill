from __future__ import annotations

import pytest
from pydantic import ValidationError

from src.api.critique_examples import CRITIQUE_EXAMPLES
from src.schemas.critique import (
    ApproachSubmission,
    BruteForceSubmission,
    ClarifySubmission,
    CoachConstraints,
    CritiquePayload,
    Difficulty,
    OptimizeSubmission,
    ProblemContext,
    PseudocodeSubmission,
    Stage,
)

PROBLEM_CONTEXT = ProblemContext(
    title="Two Sum",
    description="Find two numbers that add up to target.",
    tags=["array"],
    constraints=["1 <= nums.length <= 10^4"],
    difficulty=Difficulty.EASY,
    signature=None,
)

COACH_CONSTRAINTS = CoachConstraints(
    no_code=True,
    no_solution_reveal=True,
    feedback_style="socratic",
    max_questions=2,
    max_tokens=500,
)


def _payload(**overrides) -> CritiquePayload:
    data = {
        "session_id": "session-1",
        "stage": Stage.CLARIFY,
        "rubric": [{"key": "inputs_outputs", "max_score": 3, "expectation": "Clear I/O"}],
        "problem_context": PROBLEM_CONTEXT,
        "submission": {
            "inputs_outputs": "nums and target in, indices out",
            "constraints": "one solution",
            "examples": "Example 1",
        },
        "auto_signals": {},
        "coach_constraints": COACH_CONSTRAINTS,
    }
    data.update(overrides)
    return CritiquePayload(**data)


def test_accepts_clarify_submission() -> None:
    payload = _payload()

    assert isinstance(payload.submission, ClarifySubmission)
    assert payload.submission.inputs_outputs == "nums and target in, indices out"


def test_accepts_approach_submission() -> None:
    payload = _payload(
        stage=Stage.APPROACH,
        rubric=[{"key": "strategy", "max_score": 2, "expectation": "Clear idea"}],
        submission={
            "strategy": "Hash map of seen values",
            "justification": "Complement is recoverable in O(1)",
            "complexity": "Time O(n), space O(n)",
        },
        auto_signals=[],
    )

    assert isinstance(payload.submission, ApproachSubmission)
    assert payload.submission.strategy == "Hash map of seen values"
    assert payload.auto_signals == {}


def test_accepts_pseudocode_submission() -> None:
    payload = _payload(
        stage=Stage.PSEUDOCODE,
        rubric=[{"key": "step_order", "max_score": 3, "expectation": "Ordered steps"}],
        submission={"steps_text": "Walk the array and store seen values."},
    )

    assert isinstance(payload.submission, PseudocodeSubmission)


def test_accepts_brute_force_submission() -> None:
    payload = _payload(
        stage=Stage.BRUTE_FORCE,
        rubric=[{"key": "correctness", "max_score": 3, "expectation": "Naive solution"}],
        submission={"code": "def twoSum(nums, target): pass", "lang": "python"},
    )

    assert isinstance(payload.submission, BruteForceSubmission)


def test_accepts_optimize_submission() -> None:
    payload = _payload(
        stage=Stage.OPTIMIZE,
        rubric=[{"key": "optimization", "max_score": 2, "expectation": "Better algorithm"}],
        submission={
            "code": "def twoSum(nums, target): pass",
            "lang": "python",
            "complexity_analysis": "Time O(n), space O(n)",
            "optimization_technique": "Hash map",
            "tradeoffs": "Extra memory for faster lookups",
        },
    )

    assert isinstance(payload.submission, OptimizeSubmission)


def test_rejects_approach_stage_with_clarify_fields() -> None:
    with pytest.raises(ValidationError, match="ApproachSubmission"):
        _payload(
            stage=Stage.APPROACH,
            rubric=[{"key": "strategy", "max_score": 2, "expectation": "Clear idea"}],
            submission={
                "inputs_outputs": "nums and target",
                "constraints": "one solution",
                "examples": "Example 1",
            },
        )


def test_rejects_extra_submission_fields() -> None:
    with pytest.raises(ValidationError):
        _payload(
            stage=Stage.APPROACH,
            rubric=[{"key": "strategy", "max_score": 2, "expectation": "Clear idea"}],
            submission={
                "strategy": "Hash map",
                "justification": "Complement lookup",
                "complexity": "O(n)",
                "code": "def twoSum(): pass",
            },
        )


def test_openapi_examples_match_critique_payload() -> None:
    stages = [example["value"]["stage"] for example in CRITIQUE_EXAMPLES]

    assert stages == [
        Stage.CLARIFY,
        Stage.APPROACH,
        Stage.PSEUDOCODE,
        Stage.BRUTE_FORCE,
        Stage.OPTIMIZE,
    ]
    for example in CRITIQUE_EXAMPLES:
        CritiquePayload.model_validate(example["value"])
