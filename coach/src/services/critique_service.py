from src.schemas.critique import CritiquePayload, CritiqueResponse, Stage
from src.services.prompt_builder import PromptBuilder
from src.services.llm_client import LlmClient
from src.services.model_router import ModelRouter
import json


class CritiqueService:
    def __init__(self):
        self.model_router = ModelRouter()
        self.llm_client = LlmClient()
        self.prompt_builder = PromptBuilder()

    def critique(self, payload: CritiquePayload) -> CritiqueResponse:
        stage = payload.stage
        messages = self.prompt_builder.build(payload)
        model_config = self.model_router.get_model_config(stage, payload.problem_context.difficulty)
        response = self.llm_client.call_model(model_config, messages)
        data = json.loads(response)
        data = CritiqueResponse.model_validate(data)
        # TODO: Sanitize response
        # return CritiqueResponse(
        #     scores={
        #         "inputs_outputs": {"score": 1, "max_score": 3, "reason": "Stub"},
        #         "constraints": {"score": 1, "max_score": 3, "reason": "Stub"},
        #         "examples": {"score": 1, "max_score": 6, "reason": "Stub"},
        #     },
        #     coach_msg="Your clarification is detailed enough. Please provide more detail.",
        #     flags={
        #         "too_vague": False,
        #         "code_leak_blocked": False,
        #         "prompt_injection_detected": False,
        #     },
        #     questions=["What is the input format?", "What is the output format?"],
        # )
        print(data)
        return data

