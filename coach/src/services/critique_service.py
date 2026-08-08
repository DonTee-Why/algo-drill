from src.schemas.critique import CritiquePayload, CritiqueResponse, Stage
from src.services.prompt_builder_service import PromptBuilderService
from src.services.llm_client_service import LlmClientService
from src.services.model_route_service import ModelRouteService
import json


class CritiqueService:
    def __init__(self):
        self.model_router = ModelRouteService()
        self.llm_client = LlmClientService()
        self.prompt_builder = PromptBuilderService()

    def critique(self, payload: CritiquePayload) -> CritiqueResponse:
        stage = payload.stage
        messages = self.prompt_builder.build(payload)
        model_config = self.model_router.get_model_config(stage, payload.problem_context.difficulty)
        response = self.llm_client.call_model(model_config, messages)
        # TODO: Parse response
        data = json.loads(response)
        data = CritiqueResponse.model_validate(data)
        # TODO: Sanitize response
        # TODO: Return response
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

