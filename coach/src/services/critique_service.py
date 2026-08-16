from src.schemas.critique import CritiquePayload, CritiqueResponse
from src.services.llm_client import LlmClient
from src.services.model_router import ModelRouter
from src.services.prompt_builder import PromptBuilder
from src.services.rubric_parser import RubricParser
from src.services.sanitizer import Sanitizer


class CritiqueService:
    def __init__(self):
        self.model_router = ModelRouter()
        self.llm_client = LlmClient()
        self.prompt_builder = PromptBuilder()
        self.rubric_parser = RubricParser()
        self.sanitizer = Sanitizer()

    def critique(self, payload: CritiquePayload) -> CritiqueResponse:
        stage = payload.stage
        messages = self.prompt_builder.build(payload)
        model_config = self.model_router.get_model_config(
            stage, payload.problem_context.difficulty
        )
        model_response = self.llm_client.call_model(model_config, messages)
        data = self.rubric_parser.parse(
            model_response,
            payload.rubric,
            max_questions=payload.coach_constraints.max_questions,
        )
        return self.sanitizer.sanitize(data, stage)