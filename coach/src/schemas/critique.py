from enum import Enum
from typing import Any, Self

from pydantic import BaseModel, ConfigDict, Field, field_validator, model_validator


class Difficulty(str, Enum):
    EASY = "Easy"
    MEDIUM = "Medium"
    HARD = "Hard"


class Stage(str, Enum):
    CLARIFY = "CLARIFY"
    APPROACH = "APPROACH"
    PSEUDOCODE = "PSEUDOCODE"
    BRUTE_FORCE = "BRUTE_FORCE"
    OPTIMIZE = "OPTIMIZE"
    DONE = "DONE"


class ProblemSignature(BaseModel):
    function_name: str = Field(..., description="The function name of the problem")
    params: list[dict[str, str]] | dict[str, str] = Field(..., description="The params of the problem")
    returns: dict[str, str] = Field(..., description="The returns of the problem")


class ProblemContext(BaseModel):
    title: str = Field(..., description="The title of the problem")
    description: str = Field(..., description="The description of the problem")
    tags: list[str] = Field(..., description="The tags of the problem")
    constraints: list[str] = Field(..., description="The constraints of the problem")
    difficulty: Difficulty = Field(..., description="The difficulty of the problem")
    signature: ProblemSignature | None = Field(None, description="The signature of the problem")


class ClarifySubmission(BaseModel):
    model_config = ConfigDict(extra="forbid")

    inputs_outputs: str = Field(..., description="The inputs and outputs of the submission")
    constraints: list[str] | str = Field(..., description="The constraints of the submission")
    examples: str = Field(..., description="The examples of the submission")


class ApproachSubmission(BaseModel):
    model_config = ConfigDict(extra="forbid")

    strategy: str = Field(..., description="The high-level algorithmic idea")
    justification: str = Field(..., description="Why the approach solves the problem")
    complexity: str = Field(..., description="Rough time and space complexity")


class PseudocodeSubmission(BaseModel):
    model_config = ConfigDict(extra="forbid")

    steps_text: str = Field(..., description="Ordered algorithm steps in natural language")


class BruteForceSubmission(BaseModel):
    model_config = ConfigDict(extra="forbid")

    code: str = Field(..., description="The learner's naive implementation")
    lang: str = Field(..., description="The language of the submitted code")


class OptimizeSubmission(BaseModel):
    model_config = ConfigDict(extra="forbid")

    code: str = Field(..., description="The learner's optimized implementation")
    lang: str = Field(..., description="The language of the submitted code")
    complexity_analysis: str = Field(..., description="Claimed time and space complexity")
    optimization_technique: str = Field(..., description="The optimization technique used")
    tradeoffs: str = Field(..., description="Time and space tradeoffs")


Submission = (
    ClarifySubmission
    | ApproachSubmission
    | PseudocodeSubmission
    | BruteForceSubmission
    | OptimizeSubmission
)

SUBMISSION_BY_STAGE: dict[Stage, type[BaseModel]] = {
    Stage.CLARIFY: ClarifySubmission,
    Stage.APPROACH: ApproachSubmission,
    Stage.PSEUDOCODE: PseudocodeSubmission,
    Stage.BRUTE_FORCE: BruteForceSubmission,
    Stage.OPTIMIZE: OptimizeSubmission,
}


class CoachConstraints(BaseModel):
    no_code: bool = Field(..., description="Whether the coach should not code")
    no_solution_reveal: bool = Field(..., description="Whether the coach should not reveal the solution")
    feedback_style: str = Field(..., description="The feedback style of the coach")
    max_questions: int = Field(..., description="The maximum number of questions the coach should ask")
    max_tokens: int = Field(..., description="The maximum number of tokens the coach should use")


class CritiquePayload(BaseModel):
    model_config = ConfigDict(extra="forbid")

    session_id: str = Field(..., description="The session id")
    stage: Stage = Field(..., description="The stage of the session")
    rubric: list[dict[str, int | str | list[str]]] = Field(..., description="The rubric of the session")
    problem_context: ProblemContext = Field(..., description="The problem context")
    submission: Submission = Field(..., description="The user's submission for the current stage")
    auto_signals: dict[str, Any] = Field(default_factory=dict, description="The auto signals")
    coach_constraints: CoachConstraints = Field(..., description="The coach constraints")

    @field_validator("auto_signals", mode="before")
    @classmethod
    def coerce_auto_signals(cls, value: object) -> object:
        if value in (None, []):
            return {}

        return value

    @model_validator(mode="after")
    def submission_matches_stage(self) -> Self:
        expected = SUBMISSION_BY_STAGE.get(self.stage)
        if expected is not None and not isinstance(self.submission, expected):
            raise ValueError(
                f"submission for stage {self.stage.value} must match {expected.__name__}"
            )

        return self


class ScoreDetail(BaseModel):
    model_config = ConfigDict(extra="forbid")

    score: int = Field(..., description="The score for this rubric key")
    max_score: int | None = Field(None, description="The max score for this rubric key")
    reason: str | None = Field(None, description="Short reason for the score")


class CritiqueResponse(BaseModel):
    model_config = ConfigDict(extra="forbid")

    scores: dict[str, ScoreDetail] = Field(..., description="Per-rubric score details")
    coach_msg: str = Field(..., description="The coach message")
    flags: dict[str, bool] = Field(..., description="Coach signal flags")
    questions: list[str] = Field(..., description="Socratic follow-up questions")
