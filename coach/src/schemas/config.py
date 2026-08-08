from __future__ import annotations

from src.schemas.critique import Difficulty
from pydantic import BaseModel, ConfigDict, Field
from enum import Enum
from typing import Optional, Literal

class ModelConfig(BaseModel):
    model_config = ConfigDict(extra="forbid")

    provider: str
    vendor: Literal["openai", "moonshot"]
    model: str
    api_key: Optional[str] = None
    base_url: Optional[str] = None
    temperature: float
    max_tokens: int
    reasoning_effort: Optional[Literal["low", "medium", "high"]] = None
    alternative_models: dict[Difficulty, ModelConfig] = Field(default_factory=dict)

    def get_alternative_model(self, difficulty: Difficulty) -> ModelConfig:
        return self.alternative_models.get(difficulty, self)