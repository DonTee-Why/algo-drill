from typing import List
from src.schemas.config import ModelConfig
from src.schemas.messages import Message
from openai import OpenAI

class LlmClientService:
    def get_client(self, model_config: ModelConfig):
        if model_config.provider == "openai_compatible":
            kwargs = {
                "api_key": model_config.api_key,
                "timeout": 30,
            }

            if model_config.base_url is not None:
                kwargs["base_url"] = model_config.base_url

            return OpenAI(**kwargs)
        else:
            raise ValueError(f"Invalid provider: {model_config.provider}")

    def call_model(self, model_config: ModelConfig, messages: List[Message]) -> str:
        client = self.get_client(model_config)
        payload = {
            "model": model_config.model,
            "messages": [message.model_dump() for message in messages],
            "temperature": model_config.temperature,
            "max_tokens": model_config.max_tokens,
            "response_format": {"type": "json_object"}
        }

        if model_config.reasoning_effort is not None:
            payload["reasoning_effort"] = model_config.reasoning_effort

        response = client.chat.completions.create(**payload)
        content = response.choices[0].message.content

        if not content:
            raise RuntimeError("LLM returned empty response")

        return content
