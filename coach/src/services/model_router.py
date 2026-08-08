from dotenv import load_dotenv
from src.schemas.config import ModelConfig
from src.schemas.critique import Difficulty, Stage
import os

load_dotenv()

VENDOR_API_KEYS = {
    "openai": "OPENAI_API_KEY",
    "moonshot": "MOONSHOT_API_KEY",
}


def require_env(name: str) -> str:
    value = os.getenv(name)
    if not value:
        raise RuntimeError(f"Missing required env var: {name}")
    return value


STAGE_MODELS = {
    Stage.CLARIFY: ModelConfig(
        provider="openai_compatible",
        vendor="openai",
        model="gpt-5.6-luna",
        temperature=0.2,
        max_tokens=400),  # cheap and fast
    Stage.APPROACH: ModelConfig(
        provider="openai_compatible",
        vendor="openai",
        model="gpt-4.1-2025-04-14",
        temperature=0.2,
        max_tokens=400),  # medium reasoning
    Stage.PSEUDOCODE: ModelConfig(
        provider="openai_compatible",
        vendor="openai",
        model="gpt-5.6-terra",
        temperature=0.2,
        max_tokens=500),  # medium/high reasoning
    Stage.BRUTE_FORCE: ModelConfig(
        provider="openai_compatible",
        vendor="moonshot",
        model="kimi-k2.7-code",
        base_url="https://api.moonshot.ai/v1",
        temperature=1,  # 1 for kimi models
        max_tokens=500),  # cheap
    Stage.OPTIMIZE: ModelConfig(
        provider="openai_compatible",
        vendor="moonshot",
        model="kimi-k2.7-code",
        base_url="https://api.moonshot.ai/v1",
        temperature=1,  # 1 for kimi models
        max_tokens=800,
        alternative_models={
            Difficulty.MEDIUM: ModelConfig(
                provider="openai_compatible",
                vendor="moonshot",
                model="kimi-k3",
                base_url="https://api.moonshot.ai/v1",
                temperature=1,
                max_tokens=800),
            Difficulty.HARD: ModelConfig(
                provider="openai_compatible",
                vendor="openai",
                model="gpt-5.6",
                temperature=0.85,
                max_tokens=800,
                reasoning_effort="medium",
            ),
        }),  # high reasoning
}


class ModelRouter:
    def get_model_config(self, stage: Stage, difficulty: Difficulty | None = None) -> ModelConfig:
        try:
            model_config = STAGE_MODELS[stage]
        except KeyError:
            raise ValueError(f"No model configured for stage: {stage}")

        if difficulty is not None:
            model_config = model_config.get_alternative_model(difficulty)

        if model_config.api_key is not None:
            return model_config

        return model_config.model_copy(
            update={"api_key": require_env(VENDOR_API_KEYS[model_config.vendor])}
        )
