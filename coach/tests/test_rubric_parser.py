from __future__ import annotations

import json

import pytest

from src.services.rubric_parser import (
    COACH_MSG_MAX_LEN,
    DEFAULT_COACH_MSG,
    FALLBACK_COACH_MSG,
    SANITIZER_FLAGS,
    RubricParser,
)

CLARIFY_RUBRIC = [
    {
        "key": "inputs_outputs",
        "max_score": 3,
        "expectation": "Identify inputs and outputs.",
    },
    {
        "key": "constraints",
        "max_score": 3,
        "expectation": "Mention constraints.",
    },
    {
        "key": "examples",
        "max_score": 6,
        "expectation": "Provide examples.",
    },
]


def _raw_response(**overrides) -> dict:
    data = {
        "coach_msg": "Your inputs are clear. Add an edge-case example.",
        "scores": {
            "inputs_outputs": {
                "score": 3,
                "max_score": 3,
                "reason": "Contract is clear",
            },
            "constraints": {
                "score": 2,
                "max_score": 3,
                "reason": "Mostly covered",
            },
            "examples": {
                "score": 4,
                "max_score": 6,
                "reason": "Needs an edge case",
            },
        },
        "flags": {
            "too_vague": False,
            "code_leak_blocked": True,
            "prompt_injection_detected": False,
        },
        "questions": ["What happens with an empty array?"],
    }
    data.update(overrides)
    return data


@pytest.fixture
def parser() -> RubricParser:
    return RubricParser()


def test_parse_happy_path(parser: RubricParser) -> None:
    result = parser.parse(json.dumps(_raw_response()), CLARIFY_RUBRIC, max_questions=2)

    assert result.coach_msg == "Your inputs are clear. Add an edge-case example."
    assert result.scores["inputs_outputs"].score == 3
    assert result.scores["examples"].max_score == 6
    assert result.flags["too_vague"] is False
    assert result.flags["code_leak_blocked"] is False
    assert result.flags["invalid_json"] is False
    assert result.flags["fallback_used"] is False
    assert result.flags["missing_scores"] is False
    assert result.questions == ["What happens with an empty array?"]


def test_parse_strips_markdown_fence(parser: RubricParser) -> None:
    fenced = "```json\n" + json.dumps(_raw_response()) + "\n```"
    result = parser.parse(fenced, CLARIFY_RUBRIC)

    assert result.coach_msg is not None
    assert "inputs_outputs" in result.scores
    assert result.flags["fallback_used"] is False


def test_recovers_json_object_from_prose(parser: RubricParser) -> None:
    messy = (
        "Sure — here you go:\n"
        + json.dumps(_raw_response())
        + "\nHope that helps!"
    )
    result = parser.parse(messy, CLARIFY_RUBRIC)

    assert result.coach_msg == "Your inputs are clear. Add an edge-case example."
    assert result.scores["inputs_outputs"].score == 3
    assert result.flags["invalid_json"] is False
    assert result.flags["fallback_used"] is False


def test_clamp_scores_to_rubric_max(parser: RubricParser) -> None:
    raw = _raw_response(
        scores={
            "inputs_outputs": {"score": 99, "max_score": 3, "reason": "too high"},
            "constraints": {"score": -4, "max_score": 3, "reason": "too low"},
            "examples": {"score": 6, "max_score": 6, "reason": "ok"},
        }
    )
    result = parser.parse(json.dumps(raw), CLARIFY_RUBRIC)

    assert result.scores["inputs_outputs"].score == 3
    assert result.scores["constraints"].score == 0
    assert result.scores["examples"].score == 6


def test_ensures_every_rubric_criterion_exists(parser: RubricParser) -> None:
    raw = _raw_response(
        scores={
            "inputs_outputs": {
                "score": 2,
                "max_score": 3,
                "reason": "ok",
            },
            "extra_key": {
                "score": 1,
                "max_score": 3,
                "reason": "should be dropped",
            },
        }
    )
    result = parser.parse(json.dumps(raw), CLARIFY_RUBRIC)

    assert set(result.scores) == {"inputs_outputs", "constraints", "examples"}
    assert result.scores["constraints"].score == 0
    assert result.scores["constraints"].reason == "Missing from model response"
    assert "extra_key" not in result.scores
    assert result.flags["missing_scores"] is True
    assert result.flags["fallback_used"] is False
    assert result.flags["invalid_json"] is False


def test_normalize_flags_defaults_and_booleans(parser: RubricParser) -> None:
    raw = _raw_response(
        flags={
            "too_vague": 1,
            "missing_edge_case": "yes",
            "invalid_json": True,
            "fallback_used": True,
            "missing_scores": True,
        }
    )
    result = parser.parse(json.dumps(raw), CLARIFY_RUBRIC)

    assert result.flags["too_vague"] is True
    assert result.flags["code_leak_blocked"] is False
    assert result.flags["prompt_injection_detected"] is False
    assert result.flags["missing_edge_case"] is True
    assert result.flags["invalid_json"] is False
    assert result.flags["fallback_used"] is False
    assert result.flags["missing_scores"] is False
    for key in SANITIZER_FLAGS:
        assert result.flags[key] is False


def test_parser_resets_llm_sanitizer_flags(parser: RubricParser) -> None:
    raw = _raw_response(
        flags={
            "code_leak_blocked": True,
            "fenced_code_detected": True,
            "inline_code_removed": True,
            "syntactic_code_detected": True,
            "code_span_removed": True,
        }
    )
    result = parser.parse(json.dumps(raw), CLARIFY_RUBRIC)

    for key in SANITIZER_FLAGS:
        assert result.flags[key] is False


def test_normalize_questions_truncates(parser: RubricParser) -> None:
    raw = _raw_response(
        questions=["Q1", "Q2", "Q3", ""],
    )
    result = parser.parse(json.dumps(raw), CLARIFY_RUBRIC, max_questions=2)

    assert result.questions == ["Q1", "Q2"]


def test_falls_back_on_invalid_json(parser: RubricParser) -> None:
    result = parser.parse("{not-json", CLARIFY_RUBRIC)

    assert result.coach_msg == FALLBACK_COACH_MSG
    assert result.questions == []
    assert set(result.scores) == {"inputs_outputs", "constraints", "examples"}
    assert all(detail.score == 0 for detail in result.scores.values())
    assert result.flags["invalid_json"] is True
    assert result.flags["fallback_used"] is True
    assert result.flags["missing_scores"] is True


def test_falls_back_on_missing_top_level_keys(parser: RubricParser) -> None:
    result = parser.parse(json.dumps({"coach_msg": "hi"}), CLARIFY_RUBRIC)

    assert result.coach_msg == FALLBACK_COACH_MSG
    assert result.flags["invalid_json"] is True
    assert result.flags["fallback_used"] is True
    assert result.flags["missing_scores"] is True
    assert all(detail.score == 0 for detail in result.scores.values())


def test_falls_back_on_empty_response(parser: RubricParser) -> None:
    result = parser.parse("   ", CLARIFY_RUBRIC)

    assert result.flags["invalid_json"] is True
    assert result.flags["fallback_used"] is True


def test_defaults_empty_coach_msg(parser: RubricParser) -> None:
    result = parser.parse(json.dumps(_raw_response(coach_msg="   ")), CLARIFY_RUBRIC)

    assert result.coach_msg == DEFAULT_COACH_MSG
    assert result.flags["fallback_used"] is False


def test_defaults_non_string_coach_msg(parser: RubricParser) -> None:
    result = parser.parse(json.dumps(_raw_response(coach_msg=123)), CLARIFY_RUBRIC)

    assert result.coach_msg == DEFAULT_COACH_MSG


def test_truncates_long_coach_msg(parser: RubricParser) -> None:
    long_msg = "a" * (COACH_MSG_MAX_LEN + 50)
    result = parser.parse(json.dumps(_raw_response(coach_msg=long_msg)), CLARIFY_RUBRIC)

    assert result.coach_msg.endswith("...")
    assert len(result.coach_msg) == COACH_MSG_MAX_LEN + 3
