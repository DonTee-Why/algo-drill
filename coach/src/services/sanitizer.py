from __future__ import annotations

import re

from src.schemas.critique import CritiqueResponse, ScoreDetail, Stage

FENCE_RE = re.compile(r"```[^\n]*\n[\s\S]*?(?:```|$)|```[\s\S]*?```")
INLINE_CODE_RE = re.compile(r"`([^`]+)`")
SYNTACTIC_CODE_RE = re.compile(
    r"""
    (?:
        \bdef\s+\w+\s*\(
        |\bclass\s+\w+\s*[:(\{]
        |\bfunction\s+\w*\s*\(
        |\b(?:const|let|var)\s+\w+\s*=
        |\bimport\s+(?:\w+|[\{\*])
        |\bfrom\s+\w[\w.]*\s+import\b
        |\bfor\s*\([^)]+\)
        |\bfor\s+\w+(?:\s*,\s*\w+)*\s+in\s+(?:range|enumerate|zip)\b
        |\bfor\s+\w+(?:\s*,\s*\w+)*\s+in\s+\w+(?:\s*\([^)]*\))?\s*:
        |\bwhile\s*\(
        |\breturn\s+[\[\{\(]
        |\bconsole\.log\s*\(
        |\bprint\s*\(
        |=>\s*[{\(\$\[]
        |\bfn\s*\(
        |\bforeach\s*\(
        |<\?php
        |\$[A-Za-z_]\w*\s*=
        |\b(?:public|private|protected|static)\s+\w+
        |\bnew\s+\w+\s*\(
        |\w+\s*=\s*(?:\{|\[|new\b)
    )
    """,
    re.VERBOSE | re.IGNORECASE,
)
OPERATOR_CHARS = set("(){}[];=")
CODE_LIKE_TOKEN_LIMIT = 12
RETURN_LINE_RE = re.compile(r"^\s*return\s+\S", re.IGNORECASE)
BIG_O_RE = re.compile(r"^O\s*\([^)]+\)$", re.IGNORECASE)
INLINE_CALL_RE = re.compile(r"\w\s*\(")
INLINE_ARITHMETIC_RE = re.compile(r"[+\-*/]")
LEAK_FLAG_KEYS = (
    "fenced_code_detected",
    "inline_code_removed",
    "syntactic_code_detected",
    "code_span_removed",
)
BLOCKED_COACH_MSG = (
    "I can't share code here. Stay with the current stage and describe "
    "the idea in words instead."
)
STAGE_FALLBACKS = {
    Stage.CLARIFY: (
        "I can't provide implementation details here. Restate the input, "
        "output, constraints, and one edge-case example in your own words."
    ),
    Stage.APPROACH: (
        "I can't give the solution path directly. Describe the strategy at a "
        "higher level: what information would you track, and why?"
    ),
    Stage.PSEUDOCODE: (
        "I can't write the algorithm for you. Outline the next decision point "
        "and the edge case your steps must handle."
    ),
    Stage.BRUTE_FORCE: (
        "I can't rewrite your code. Use the test result to identify which "
        "behavior failed."
    ),
    Stage.OPTIMIZE: (
        "I can't provide optimized code. Compare your current complexity with "
        "the target and explain what repeated work you can remove."
    ),
}
FALLBACK_QUESTION = (
    "Can you explain the next step without writing implementation details?"
)


def _empty_leak_flags() -> dict[str, bool]:
    return dict.fromkeys(LEAK_FLAG_KEYS, False)


def _merge_leak_flags(*flag_sets: dict[str, bool]) -> dict[str, bool]:
    merged = _empty_leak_flags()
    for flags in flag_sets:
        for key in LEAK_FLAG_KEYS:
            merged[key] = merged[key] or bool(flags.get(key))
    return merged


class Sanitizer:
    """Strip code-like leaks from coach output.

    This is a syntax sanitizer only. It does not catch a plain-English
    algorithm dump ("initialize a hash map, loop, compute the complement,
    return both indices"). If leak rate stays high without fences/code,
    add a separate solution-reveal heuristic and flag
    (`solution_reveal_blocked`), not a bag-of-verbs counter — APPROACH and
    PSEUDOCODE coaching legitimately uses words like loop/store/check.
    """

    def sanitize(self, response: CritiqueResponse, stage: Stage) -> CritiqueResponse:
        leak_flags = _empty_leak_flags()

        coach_msg, _, coach_flags = self._clean_text(response.coach_msg)
        leak_flags = _merge_leak_flags(leak_flags, coach_flags)

        questions: list[str] = []
        for question in response.questions:
            cleaned, question_blocked, question_flags = self._clean_text(question)
            leak_flags = _merge_leak_flags(leak_flags, question_flags)
            if not question_blocked and cleaned:
                questions.append(cleaned)

        scores: dict[str, ScoreDetail] = {}
        for key, detail in response.scores.items():
            reason = detail.reason
            if reason:
                reason, _, reason_flags = self._clean_text(reason)
                leak_flags = _merge_leak_flags(leak_flags, reason_flags)
                reason = reason or None
            scores[key] = ScoreDetail(
                score=detail.score,
                max_score=detail.max_score,
                reason=reason,
            )

        blocked = any(leak_flags.values())
        if blocked and not coach_msg:
            coach_msg = STAGE_FALLBACKS.get(stage, BLOCKED_COACH_MSG)
        if blocked and not questions:
            questions = [FALLBACK_QUESTION]

        flags = dict(response.flags)
        flags["code_leak_blocked"] = blocked
        flags.update(leak_flags)

        return CritiqueResponse(
            coach_msg=coach_msg,
            scores=scores,
            flags=flags,
            questions=questions,
        )

    def _clean_text(self, text: str) -> tuple[str, bool, dict[str, bool]]:
        if not text or not text.strip():
            return "", False, _empty_leak_flags()

        flags = _empty_leak_flags()
        cleaned, fence_count = FENCE_RE.subn("", text)
        if fence_count:
            flags["fenced_code_detected"] = True

        cleaned, inline_flags = self._strip_inline_code(cleaned)
        cleaned, span_flags = self._strip_code_spans(cleaned)
        flags = _merge_leak_flags(flags, inline_flags, span_flags)
        blocked = any(flags.values())

        return self._collapse_whitespace(cleaned), blocked, flags

    def _strip_inline_code(self, text: str) -> tuple[str, dict[str, bool]]:
        flags = _empty_leak_flags()

        def replace(match: re.Match[str]) -> str:
            inner = match.group(1)
            if self._is_syntactic(inner):
                flags["inline_code_removed"] = True
                flags["syntactic_code_detected"] = True
                return ""
            if self._is_unsafe_inline(inner) or self._is_long_code_like(inner):
                flags["inline_code_removed"] = True
                return ""
            return match.group(0)

        return INLINE_CODE_RE.sub(replace, text), flags

    def _strip_code_spans(self, text: str) -> tuple[str, dict[str, bool]]:
        lines = text.split("\n")
        kept: list[str] = []
        flags = _empty_leak_flags()
        index = 0

        while index < len(lines):
            if not self._line_looks_like_code(lines[index]):
                kept.append(lines[index])
                index += 1
                continue

            end = index + 1
            while end < len(lines):
                candidate = lines[end]
                if self._line_looks_like_code(candidate):
                    end += 1
                    continue
                if (
                    not candidate.strip()
                    and end + 1 < len(lines)
                    and self._line_looks_like_code(lines[end + 1])
                ):
                    end += 1
                    continue
                break

            span_lines = lines[index:end]
            span = "\n".join(span_lines).strip()
            code_line_count = sum(
                1 for line in span_lines if self._line_looks_like_code(line)
            )
            if (
                self._is_syntactic(span)
                or self._is_long_code_like(span)
                or self._is_code_return_line(span)
                or code_line_count >= 2
            ):
                flags["code_span_removed"] = True
                if self._is_syntactic(span):
                    flags["syntactic_code_detected"] = True
            else:
                kept.extend(span_lines)
            index = end

        return "\n".join(kept), flags

    def _line_looks_like_code(self, line: str) -> bool:
        stripped = line.strip()
        if not stripped:
            return False

        if self._is_syntactic(stripped):
            return True

        if self._is_code_return_line(line):
            return True

        if re.fullmatch(r"[\{\}\);]+", stripped):
            return True

        indented = re.match(r"^[ \t]{2,}", line) is not None
        if stripped.endswith((";", "{", "}", ":")) and (
            "(" in stripped or "=" in stripped or indented
        ):
            return True

        if indented and any(ch in stripped for ch in "()=[]"):
            return True

        operators = sum(ch in OPERATOR_CHARS for ch in stripped)
        density = operators / len(stripped)
        return operators >= 6 and density >= 0.18 and len(stripped) > 20

    def _is_syntactic(self, text: str) -> bool:
        return SYNTACTIC_CODE_RE.search(text) is not None

    def _is_code_return_line(self, text: str) -> bool:
        if not RETURN_LINE_RE.search(text):
            return False

        stripped = text.strip()
        indented = re.match(r"^[ \t]{2,}", text) is not None
        return indented or "[" in stripped or "]" in stripped or ";" in stripped

    def _is_unsafe_inline(self, text: str) -> bool:
        inner = text.strip()
        if not inner or BIG_O_RE.fullmatch(inner):
            return False

        return (
            "[" in inner
            or "]" in inner
            or INLINE_CALL_RE.search(inner) is not None
            or INLINE_ARITHMETIC_RE.search(inner) is not None
        )

    def _is_long_code_like(self, text: str) -> bool:
        if self._token_count(text) <= CODE_LIKE_TOKEN_LIMIT:
            return False

        operators = sum(ch in OPERATOR_CHARS for ch in text)
        density = operators / max(len(text), 1)
        return density >= 0.18 and operators >= 6

    def _token_count(self, text: str) -> int:
        return len(text.split())

    def _collapse_whitespace(self, text: str) -> str:
        text = re.sub(r"[ \t]+\n", "\n", text)
        text = re.sub(r"\n{3,}", "\n\n", text)
        text = re.sub(r"[ \t]{2,}", " ", text)
        return text.strip()
