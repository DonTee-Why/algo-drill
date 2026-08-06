from typing import Literal

from pydantic import BaseModel, Field


class Message(BaseModel):
    role: Literal["system", "user", "assistant"] = Field(..., description="Chat message role")
    content: str = Field(..., description="Chat message content")
