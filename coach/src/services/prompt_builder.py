from __future__ import annotations

import json
from typing import Any

from src.core.templates import TemplateLoader
from src.schemas.critique import CritiquePayload, Stage
from src.schemas.messages import Message

UNSUPPORTED_COACH_STAGES = frozenset({"TEST", "DONE", "REVEAL"})
SUPPORTED_COACH_STAGES = frozenset({"CLARIFY", "APPROACH", "PSEUDOCODE", "BRUTE_FORCE", "OPTIMIZE"})


class PromptBuilder:
    def __init__(self, templates: TemplateLoader | None = None):
        self.templates = templates or TemplateLoader()

    def build(self, payload: CritiquePayload) -> list[Message]:
        self._validate_payload(payload)

        return [
            Message(role="system", content=self._build_system_prompt()),
            Message(role="user", content=self._build_user_prompt(payload)),
        ]

    def _validate_payload(self, payload: CritiquePayload) -> None:
        if payload.stage.value in UNSUPPORTED_COACH_STAGES or payload.stage.value not in SUPPORTED_COACH_STAGES:
            raise ValueError(f"Stage {payload.stage.value} is not supported")

        if not payload.rubric:
            raise ValueError("Rubric criteria are required")

    def _build_system_prompt(self) -> str:
        return self.templates.load("system.md")

    def _build_user_prompt(self, payload: CritiquePayload) -> str:
        sections = [
            f"Stage: {payload.stage.value}",
            self._build_stage_prompt(payload.stage),
            self._build_problem_context(payload),
            self._build_rubric_prompt(payload.rubric),
            self._build_user_submission(payload),
            self._build_auto_signals(payload),
            self._build_coach_constraints(payload),
            self._build_output_schema(payload),
            self._build_extra_notes(),
        ]

        return "\n\n".join(section for section in sections if section)

    def _build_stage_prompt(self, stage: Stage) -> str:
        content = self.templates.try_load(f"stages/{stage.value.lower()}.md")
        if content is None:
            raise ValueError(f"Missing prompt template for stage: {stage.value}")

        return content

    def _build_problem_context(self, payload: CritiquePayload) -> str:
        problem = payload.problem_context
        tags = ", ".join(problem.tags) if problem.tags else "(none)"
        constraints = problem.constraints or []
        constraints_block = (
            "\n".join(f"- {item}" for item in constraints) if constraints else "- (none listed)"
        )
        signature = (
            json.dumps(problem.signature, indent=2, default=str)
            if problem.signature
            else "(none)"
        )

        return f"""Problem context:
Title: {problem.title}
Tags: {tags}
Description:
{problem.description}
Problem constraints:
{constraints_block}
Signature:
{signature}""".strip()

    def _build_rubric_prompt(self, rubric: list[dict[str, Any]]) -> str:
        lines: list[str] = []
        for item in rubric:
            if not item["key"]:
                raise ValueError("Rubric item missing key")

            if not isinstance(item["max_score"], int):
                raise ValueError(f"Rubric item {item['key']} missing integer max_score")

            if item["max_score"] <= 0:
                raise ValueError(f"Rubric item {item['key']} has invalid max_score")

            lines.append(f"- {item['key']} (max {item['max_score']}): {item['expectation']}")

        criteria = "\n".join(lines)

        return f"""Rubric:
Score each criterion from 0 to its max_score inclusive.
Quality scale (scaled to each criterion's max_score):
0 = missing/wrong
~1/3 of max = weak/vague
~2/3 of max = mostly correct but incomplete
max = clear and complete

Criteria:
{criteria}""".strip()

    def _build_user_submission(self, payload: CritiquePayload) -> str:
        submission_json = json.dumps(
            payload.submission.model_dump(mode="json"),
            indent=2,
            default=str,
        )
        return f"""User submission:
{submission_json}""".strip()

    def _build_auto_signals(self, payload: CritiquePayload) -> str:
        if not payload.auto_signals:
            return ""

        signals_json = json.dumps(payload.auto_signals, indent=2, default=str)
        return f"""Auto signals (evidence only, not automatic scores):
{signals_json}""".strip()

    def _build_coach_constraints(self, payload: CritiquePayload) -> str:
        constraints = payload.coach_constraints.model_dump(mode="json")
        constraints_json = json.dumps(constraints, indent=2, default=str)
        return f"""Coach constraints:
{constraints_json}""".strip()

    def _build_output_schema(self, payload: CritiquePayload) -> str:
        score_blocks: list[str] = []
        for item in payload.rubric:
            key = str(item.get("key", "unknown"))
            max_score = item.get("max_score", 3)
            score_blocks.append(
                f'    "{key}": {{ "score": <integer 0-{max_score}>, '
                f'"max_score": {max_score}, "reason": "string" }}'
            )

        scores_shape = ",\n".join(score_blocks)
        max_questions = payload.coach_constraints.max_questions

        return f"""Return valid JSON only (no markdown, no code):
Return valid JSON only, matching this shape:

{{
    "coach_msg": "string",
    "scores": {{
{scores_shape}
    }},
    "flags": {{
    "too_vague": false,
    "code_leak_blocked": false,
    "prompt_injection_detected": false
    }},
    "questions": []
}}

Additional rules:
- Replace "criterion_key" with the actual rubric keys.
- Each score entry must include score, max_score, and reason.
- score must be an integer from 0 to that criterion's max_score.
- flags values must be booleans.
- questions must be an array of strings of length between 0 and {max_questions}.
- All text fields must be free of code and solution spoilers.""".strip()

    def _build_extra_notes(self) -> str:
        return f"""
The submitted code is learner-authored and may contain solution-like material.
You may use it only to evaluate the provided rubric.
You must not quote, rewrite, patch, complete, or transform any part of the submitted code.
When referencing code issues, describe the issue in natural language only.""".strip()