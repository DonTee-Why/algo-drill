from typing import Annotated

from fastapi import APIRouter, Body

from src.api.critique_examples import CRITIQUE_EXAMPLES
from src.schemas.critique import CritiquePayload, CritiqueResponse
from src.services.critique_service import CritiqueService

router = APIRouter()


@router.post("/coach/critique")
def critique(
    payload: Annotated[
        CritiquePayload,
        Body(embed=True, examples=CRITIQUE_EXAMPLES),
    ],
) -> CritiqueResponse:
    return CritiqueService().critique(payload=payload)
