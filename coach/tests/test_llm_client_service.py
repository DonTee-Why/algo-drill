from __future__ import annotations

from types import SimpleNamespace
from unittest.mock import MagicMock, patch

import pytest

from src.schemas.config import ModelConfig
from src.schemas.messages import Message
from src.services.llm_client_service import LlmClient


def _model_config(**overrides) -> ModelConfig:
    data = {
        "provider": "openai_compatible",
        "vendor": "openai",
        "model": "gpt-test",
        "api_key": "test-key",
        "base_url": None,
        "temperature": 0.2,
        "max_tokens": 400,
        "reasoning_effort": None,
    }
    data.update(overrides)
    return ModelConfig(**data)


@pytest.fixture
def llm_client() -> LlmClient:
    return LlmClient()


def test_get_client_builds_openai_client_without_base_url(
    llm_client: LlmClient,
) -> None:
    model_config = _model_config()

    with patch("src.services.llm_client_service.OpenAI") as openai_cls:
        openai_cls.return_value = MagicMock(name="client")
        client = llm_client.get_client(model_config)

    openai_cls.assert_called_once_with(api_key="test-key", timeout=30)
    assert client is openai_cls.return_value


def test_get_client_passes_base_url_when_present(llm_client: LlmClient) -> None:
    model_config = _model_config(
        vendor="moonshot",
        base_url="https://api.moonshot.ai/v1",
    )

    with patch("src.services.llm_client_service.OpenAI") as openai_cls:
        llm_client.get_client(model_config)

    openai_cls.assert_called_once_with(
        api_key="test-key",
        timeout=30,
        base_url="https://api.moonshot.ai/v1",
    )


def test_get_client_rejects_unsupported_provider(llm_client: LlmClient) -> None:
    model_config = _model_config(provider="anthropic")

    with pytest.raises(ValueError, match="Invalid provider: anthropic"):
        llm_client.get_client(model_config)


def test_call_model_returns_message_content(llm_client: LlmClient) -> None:
    model_config = _model_config()
    messages = [
        Message(role="system", content="system prompt"),
        Message(role="user", content="user prompt"),
    ]
    mock_client = MagicMock()
    mock_client.chat.completions.create.return_value = SimpleNamespace(
        choices=[SimpleNamespace(message=SimpleNamespace(content='{"ok": true}'))]
    )

    with patch.object(llm_client, "get_client", return_value=mock_client):
        content = llm_client.call_model(model_config, messages)

    assert content == '{"ok": true}'
    mock_client.chat.completions.create.assert_called_once_with(
        model="gpt-test",
        messages=[
            {"role": "system", "content": "system prompt"},
            {"role": "user", "content": "user prompt"},
        ],
        temperature=0.2,
        max_tokens=400,
        response_format={"type": "json_object"},
    )


def test_call_model_includes_reasoning_effort_when_set(
    llm_client: LlmClient,
) -> None:
    model_config = _model_config(reasoning_effort="medium", temperature=0.85, max_tokens=300)
    messages = [Message(role="user", content="optimize this")]
    mock_client = MagicMock()
    mock_client.chat.completions.create.return_value = SimpleNamespace(
        choices=[SimpleNamespace(message=SimpleNamespace(content='{"coach_msg": "ok"}'))]
    )

    with patch.object(llm_client, "get_client", return_value=mock_client):
        llm_client.call_model(model_config, messages)

    _, kwargs = mock_client.chat.completions.create.call_args
    assert kwargs["reasoning_effort"] == "medium"
    assert kwargs["temperature"] == 0.85
    assert kwargs["max_tokens"] == 300
    assert "response_format" in kwargs


def test_call_model_rejects_empty_response(llm_client: LlmClient) -> None:
    model_config = _model_config()
    messages = [Message(role="user", content="hello")]
    mock_client = MagicMock()
    mock_client.chat.completions.create.return_value = SimpleNamespace(
        choices=[SimpleNamespace(message=SimpleNamespace(content=""))]
    )

    with patch.object(llm_client, "get_client", return_value=mock_client):
        with pytest.raises(RuntimeError, match="LLM returned empty response"):
            llm_client.call_model(model_config, messages)


def test_call_model_rejects_none_response(llm_client: LlmClient) -> None:
    model_config = _model_config()
    messages = [Message(role="user", content="hello")]
    mock_client = MagicMock()
    mock_client.chat.completions.create.return_value = SimpleNamespace(
        choices=[SimpleNamespace(message=SimpleNamespace(content=None))]
    )

    with patch.object(llm_client, "get_client", return_value=mock_client):
        with pytest.raises(RuntimeError, match="LLM returned empty response"):
            llm_client.call_model(model_config, messages)
