from __future__ import annotations

import pytest

from src.schemas.config import ModelConfig
from src.schemas.critique import Difficulty, Stage
from src.services.model_route_service import ModelRouteService


def _model(
    *,
    model: str,
    vendor: str = "openai",
    temperature: float = 0.2,
    max_tokens: int = 400,
    base_url: str | None = None,
    reasoning_effort: str | None = None,
    alternative_models: dict[Difficulty, ModelConfig] | None = None,
) -> ModelConfig:
    return ModelConfig(
        provider="openai_compatible",
        vendor=vendor,
        model=model,
        api_key="test-key",
        base_url=base_url,
        temperature=temperature,
        max_tokens=max_tokens,
        reasoning_effort=reasoning_effort,
        alternative_models=alternative_models or {},
    )


@pytest.fixture
def stage_models(monkeypatch: pytest.MonkeyPatch) -> dict[Stage, ModelConfig]:
    models = {
        Stage.CLARIFY: _model(model="clarify-model", max_tokens=400),
        Stage.APPROACH: _model(model="approach-model", max_tokens=400),
        Stage.PSEUDOCODE: _model(model="pseudocode-model", max_tokens=500),
        Stage.BRUTE_FORCE: _model(
            model="brute-force-model",
            vendor="moonshot",
            base_url="https://api.moonshot.ai/v1",
            temperature=1,
            max_tokens=500,
        ),
        Stage.OPTIMIZE: _model(
            model="optimize-default",
            vendor="moonshot",
            base_url="https://api.moonshot.ai/v1",
            temperature=1,
            max_tokens=800,
            alternative_models={
                Difficulty.MEDIUM: _model(
                    model="optimize-medium",
                    vendor="moonshot",
                    base_url="https://api.moonshot.ai/v1",
                    temperature=1,
                    max_tokens=300,
                ),
                Difficulty.HARD: _model(
                    model="optimize-hard",
                    temperature=0.85,
                    max_tokens=300,
                    reasoning_effort="medium",
                ),
            },
        ),
    }
    monkeypatch.setattr("src.services.model_route_service.STAGE_MODELS", models)
    return models


@pytest.fixture
def router(stage_models: dict[Stage, ModelConfig]) -> ModelRouteService:
    return ModelRouteService()


@pytest.mark.parametrize(
    ("stage", "expected_model"),
    [
        (Stage.CLARIFY, "clarify-model"),
        (Stage.APPROACH, "approach-model"),
        (Stage.PSEUDOCODE, "pseudocode-model"),
        (Stage.BRUTE_FORCE, "brute-force-model"),
        (Stage.OPTIMIZE, "optimize-default"),
    ],
)
def test_get_model_config_returns_default_for_stage(
    router: ModelRouteService,
    stage: Stage,
    expected_model: str,
) -> None:
    config = router.get_model_config(stage)

    assert config.model == expected_model
    assert config.provider == "openai_compatible"


def test_get_model_config_without_difficulty_ignores_alternatives(
    router: ModelRouteService,
) -> None:
    config = router.get_model_config(Stage.OPTIMIZE)

    assert config.model == "optimize-default"
    assert Difficulty.MEDIUM in config.alternative_models


@pytest.mark.parametrize(
    ("difficulty", "expected_model"),
    [
        (Difficulty.MEDIUM, "optimize-medium"),
        (Difficulty.HARD, "optimize-hard"),
        (Difficulty.EASY, "optimize-default"),
    ],
)
def test_get_model_config_selects_optimize_alternative_by_difficulty(
    router: ModelRouteService,
    difficulty: Difficulty,
    expected_model: str,
) -> None:
    config = router.get_model_config(Stage.OPTIMIZE, difficulty)

    assert config.model == expected_model


def test_get_model_config_falls_back_when_stage_has_no_alternative(
    router: ModelRouteService,
) -> None:
    config = router.get_model_config(Stage.CLARIFY, Difficulty.HARD)

    assert config.model == "clarify-model"


def test_get_model_config_rejects_unconfigured_stage(
    router: ModelRouteService,
) -> None:
    with pytest.raises(ValueError, match="No model configured for stage"):
        router.get_model_config(Stage.DONE)


def test_get_model_config_hard_optimize_includes_reasoning_effort(
    router: ModelRouteService,
) -> None:
    config = router.get_model_config(Stage.OPTIMIZE, Difficulty.HARD)

    assert config.reasoning_effort == "medium"
    assert config.temperature == 0.85
    assert config.max_tokens == 300


def test_get_model_config_resolves_api_key_lazily(
    monkeypatch: pytest.MonkeyPatch,
) -> None:
    monkeypatch.setattr(
        "src.services.model_route_service.STAGE_MODELS",
        {
            Stage.CLARIFY: ModelConfig(
                provider="openai_compatible",
                vendor="openai",
                model="clarify-model",
                temperature=0.2,
                max_tokens=400,
            ),
            Stage.BRUTE_FORCE: ModelConfig(
                provider="openai_compatible",
                vendor="moonshot",
                model="brute-force-model",
                base_url="https://api.moonshot.ai/v1",
                temperature=1,
                max_tokens=500,
            ),
        },
    )
    monkeypatch.delenv("OPENAI_API_KEY", raising=False)
    monkeypatch.delenv("MOONSHOT_API_KEY", raising=False)

    router = ModelRouteService()

    with pytest.raises(RuntimeError, match="OPENAI_API_KEY"):
        router.get_model_config(Stage.CLARIFY)

    monkeypatch.setenv("OPENAI_API_KEY", "openai-live-key")
    clarify = router.get_model_config(Stage.CLARIFY)
    assert clarify.api_key == "openai-live-key"

    with pytest.raises(RuntimeError, match="MOONSHOT_API_KEY"):
        router.get_model_config(Stage.BRUTE_FORCE)

    monkeypatch.setenv("MOONSHOT_API_KEY", "moonshot-live-key")
    brute_force = router.get_model_config(Stage.BRUTE_FORCE)
    assert brute_force.api_key == "moonshot-live-key"
