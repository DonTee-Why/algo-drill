from src.core.templates import TemplateLoader
from src.schemas.critique import CritiquePayload, Stage
from src.schemas.messages import Message

UNSUPPORTED_COACH_STAGES = frozenset({"TEST", "DONE", "REVEAL"})


class PromptBuilderService:
    def __init__(self, stage: Stage, templates: TemplateLoader | None = None):
        self.stage = stage
        self.templates = templates or TemplateLoader()

    def build(self, payload: CritiquePayload) -> list[Message]:
        self.validate_request(payload)

        return [
            Message(role="system", content=self.system_prompt()),
            Message(role="user", content=self.build_user_prompt(payload)),
        ]

    def validate_request(self, payload: CritiquePayload) -> None:
        if payload.stage.value in UNSUPPORTED_COACH_STAGES:
            raise ValueError(f"Stage {payload.stage.value} should not call coach")

        if payload.stage != self.stage:
            raise ValueError(
                f"Payload stage {payload.stage.value} does not match builder stage {self.stage.value}"
            )

        if not payload.rubric:
            raise ValueError("Rubric criteria are required")

    def system_prompt(self) -> str:
        return self.templates.load("system.md")

    def stage_prompt(self) -> str | None:
        return self.templates.try_load(f"stages/{self.stage.value.lower()}.md")

    def build_user_prompt(self, payload: CritiquePayload) -> str:
        sections = [
            f"Stage: {payload.stage.value}",
            f"Rubric:\n{payload.rubric}",
            f"Problem context:\n{payload.problem_context.model_dump(mode='json')}",
            f"Submission:\n{payload.submission.model_dump(mode='json')}",
            f"Auto signals:\n{payload.auto_signals}",
            f"Coach constraints:\n{payload.coach_constraints.model_dump(mode='json')}",
            "Evaluate the submission against the rubric and return JSON only.",
        ]

        stage_prompt = self.stage_prompt()
        if stage_prompt is not None:
            sections.insert(1, stage_prompt)

        return "\n\n".join(sections)
