from __future__ import annotations

import json
from typing import Any

from src.core.templates import TemplateLoader
from src.schemas.critique import CritiquePayload, Stage
from src.schemas.messages import Message

UNSUPPORTED_COACH_STAGES = frozenset({"TEST", "DONE", "REVEAL"})


class PromptBuilderService:
    def __init__(self, stage: Stage, templates: TemplateLoader | None = None):
        self.stage = stage
        self.templates = templates or TemplateLoader()

    def build(self, payload: CritiquePayload) -> list[Message]:
        self._validate_payload(payload)

        return [
            Message(role="system", content=self._build_system_prompt()),
            Message(role="user", content=self._build_user_prompt(payload)),
        ]

    def _validate_payload(self, payload: CritiquePayload) -> None:
        if payload.stage.value in UNSUPPORTED_COACH_STAGES:
            raise ValueError(f"Stage {payload.stage.value} should not call coach")

        if payload.stage != self.stage:
            raise ValueError(
                f"Payload stage {payload.stage.value} does not match builder stage {self.stage.value}"
            )

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
        ]

        return "\n\n".join(section for section in sections if section)

    def _build_stage_prompt(self, stage: Stage) -> str:
        content = self.templates.try_load(f"stages/{stage.value.lower()}.md")
        if content is None:
            return ""

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
            key = str(item.get("key", "unknown"))
            max_score = item.get("max_score", 3)
            expectation = item.get("expectation", "")
            lines.append(f"- {key} (max {max_score}): {expectation}")

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
{{
  "coach_msg": "string, 1-3 sentences, no markdown, no code",
  "scores": {{
{scores_shape}
  }},
  "flags": {{
    "too_vague": false,
    "code_leak_blocked": false,
    "prompt_injection_detected": false
  }},
  "questions": ["string"]  // length 0..{max_questions}
}}

Rules:
- Include every rubric key in scores, and only those keys.
- Each score entry must include score, max_score, and reason.
- flags values must be booleans.
- questions must be an array of strings with length 0..{max_questions}.
- All text fields must be free of code and solution spoilers.""".strip()
