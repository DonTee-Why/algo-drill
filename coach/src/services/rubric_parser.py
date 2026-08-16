from __future__ import annotations

import json
import re
from typing import Any

from src.schemas.critique import CritiqueResponse, ScoreDetail

REQUIRED_TOP_LEVEL_KEYS = ("coach_msg", "scores", "flags", "questions")
REQUIRED_FLAGS = ("too_vague", "code_leak_blocked", "prompt_injection_detected")
SANITIZER_FLAGS = (
    "code_leak_blocked",
    "fenced_code_detected",
    "inline_code_removed",
    "syntactic_code_detected",
    "code_span_removed",
)
META_FLAGS = ("invalid_json", "fallback_used", "missing_scores")
FALLBACK_COACH_MSG = (
    "I could not reliably evaluate this response. Recheck the current "
    "stage requirements and make your answer more explicit."
)
DEFAULT_COACH_MSG = (
    "Your answer needs more detail for this stage. Revisit the rubric "
    "and make your reasoning clearer."
)
COACH_MSG_MAX_LEN = 800
FENCE_RE = re.compile(r"^```(?:json)?\s*([\s\S]*?)\s*```$", re.IGNORECASE)


class RubricParser:
    def parse(
        self,
        model_response: str,
        rubric: list[dict[str, Any]],
        *,
        max_questions: int | None = None,
    ) -> CritiqueResponse:
        if not rubric:
            raise ValueError("Rubric criteria are required")

        raw = self._try_parse_raw(model_response)
        if raw is None or not self._has_valid_shape(raw):
            return self._fallback(rubric, invalid_json=True)

        scores, missing_scores = self._extract_scores(raw["scores"], rubric)
        flags = self._normalize_flags(
            raw["flags"],
            missing_scores=missing_scores,
        )

        return CritiqueResponse(
            coach_msg=self._normalize_coach_msg(raw.get("coach_msg")),
            scores=scores,
            flags=flags,
            questions=self._normalize_questions(raw["questions"], max_questions),
        )

    def _try_parse_raw(self, model_response: str) -> dict[str, Any] | None:
        text = model_response.strip()
        if not text:
            return None

        fenced = FENCE_RE.match(text)
        if fenced:
            text = fenced.group(1).strip()

        parsed = self._loads_object(text)
        if parsed is not None:
            return parsed

        return self._recover_json_object(text)

    def _loads_object(self, text: str) -> dict[str, Any] | None:
        try:
            parsed = json.loads(text)
        except json.JSONDecodeError:
            return None

        return parsed if isinstance(parsed, dict) else None

    def _recover_json_object(self, text: str) -> dict[str, Any] | None:
        match = re.search(r"\{.*\}", text, flags=re.DOTALL)
        if not match:
            return None

        return self._loads_object(match.group(0))

    def _has_valid_shape(self, raw: dict[str, Any]) -> bool:
        if any(key not in raw for key in REQUIRED_TOP_LEVEL_KEYS):
            return False

        return (
            isinstance(raw["scores"], dict)
            and isinstance(raw["flags"], dict)
            and isinstance(raw["questions"], list)
        )

    def _fallback(
        self,
        rubric: list[dict[str, Any]],
        *,
        invalid_json: bool,
    ) -> CritiqueResponse:
        scores = {
            key: ScoreDetail(
                score=0,
                max_score=max_score,
                reason="Unavailable due to invalid model response",
            )
            for key, max_score in self._rubric_max_scores(rubric).items()
        }

        return CritiqueResponse(
            coach_msg=FALLBACK_COACH_MSG,
            scores=scores,
            flags=self._normalize_flags(
                {},
                invalid_json=invalid_json,
                fallback_used=True,
                missing_scores=True,
            ),
            questions=[],
        )

    def _normalize_coach_msg(self, coach_msg: Any) -> str:
        if not isinstance(coach_msg, str) or not coach_msg.strip():
            return DEFAULT_COACH_MSG

        msg = coach_msg.strip()
        if len(msg) > COACH_MSG_MAX_LEN:
            return msg[:COACH_MSG_MAX_LEN].rstrip() + "..."

        return msg

    def _extract_scores(
        self,
        raw_scores: dict[str, Any],
        rubric: list[dict[str, Any]],
    ) -> tuple[dict[str, ScoreDetail], bool]:
        rubric_max_scores = self._rubric_max_scores(rubric)
        scores: dict[str, ScoreDetail] = {}
        missing_scores = False

        for key, max_score in rubric_max_scores.items():
            entry = raw_scores.get(key)
            if not isinstance(entry, dict) or "score" not in entry:
                scores[key] = ScoreDetail(
                    score=0,
                    max_score=max_score,
                    reason="Missing from model response",
                )
                missing_scores = True
                continue

            score = self._clamp_score(entry.get("score"), max_score)
            reason = entry.get("reason")
            if reason is not None and not isinstance(reason, str):
                reason = None

            scores[key] = ScoreDetail(
                score=score,
                max_score=max_score,
                reason=reason.strip() if isinstance(reason, str) else reason,
            )

        return scores, missing_scores

    def _rubric_max_scores(self, rubric: list[dict[str, Any]]) -> dict[str, int]:
        max_scores: dict[str, int] = {}
        for item in rubric:
            key = str(item.get("key", "")).strip()
            if not key:
                raise ValueError("Rubric item is missing key")

            raw_max = item.get("max_score", 3)
            try:
                max_score = int(raw_max)
            except (TypeError, ValueError) as exc:
                raise ValueError(f"Rubric item {key} has invalid max_score") from exc

            if max_score < 0:
                raise ValueError(f"Rubric item {key} max_score must be >= 0")

            max_scores[key] = max_score

        return max_scores

    def _clamp_score(self, raw_score: Any, max_score: int) -> int:
        if raw_score is None:
            return 0

        try:
            score = int(raw_score)
        except (TypeError, ValueError):
            return 0

        return max(0, min(score, max_score))

    def _normalize_flags(
        self,
        raw_flags: dict[str, Any],
        *,
        invalid_json: bool = False,
        fallback_used: bool = False,
        missing_scores: bool = False,
    ) -> dict[str, bool]:
        flags: dict[str, bool] = {}

        for key, value in raw_flags.items():
            if not isinstance(key, str) or key in META_FLAGS:
                continue
            flags[key] = bool(value)

        for key in REQUIRED_FLAGS:
            flags.setdefault(key, False)

        # Sanitizer owns leak flags; LLM must not claim a block.
        for key in SANITIZER_FLAGS:
            flags[key] = False

        flags["invalid_json"] = invalid_json
        flags["fallback_used"] = fallback_used
        flags["missing_scores"] = missing_scores

        return flags

    def _normalize_questions(
        self,
        raw_questions: list[Any],
        max_questions: int | None,
    ) -> list[str]:
        questions: list[str] = []
        for item in raw_questions:
            if not isinstance(item, str):
                continue
            cleaned = item.strip()
            if cleaned:
                questions.append(cleaned)

        if max_questions is not None:
            if max_questions < 0:
                raise ValueError("max_questions must be >= 0")
            questions = questions[:max_questions]

        return questions
