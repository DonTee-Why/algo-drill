from typing import Annotated

from fastapi import APIRouter, Body

from src.schemas.critique import CritiquePayload, CritiqueResponse, Stage
from src.services.critique_service import CritiqueService

router = APIRouter()


@router.post("/coach/critique")
def critique(payload: Annotated[CritiquePayload, Body(embed=True, examples=[
    {
        "value": {
            "session_id": "123",
            "stage": Stage.CLARIFY,
            "rubric": [{"name": "inputs_outputs", "score": 1}, {"name": "constraints", "score": 1}, {"name": "examples", "score": 1}],
            "problem_context": {
                "title": "Two Sum",
                "description": "Given an array of integers, return indices of the two numbers such that they add up to a specific target.",
                "tags": ["array", "hash table"],
                "constraints": ["O(n)", "O(1)"],
                "difficulty": "Easy",
                "signature": {
                    "function_name": "twoSum",
                    "params": [{"name": "nums", "type": "int[]"}, {"name": "target", "type": "int"}],
                    "returns": {"type": "int[]"},
                },
            },
            "submission": {"inputs_outputs": "Given an array of integers, return indices of the two numbers such that they add up to a specific target.", "constraints": "O(n)", "examples": "Given an array of integers, return indices of the two numbers such that they add up to a specific target."},
            "auto_signals": {"mentioned_param_names": ["nums", "target"], "missing_param_names": [], "example_count": 1, "has_marked_edge_case": False},
            "coach_constraints": {"no_code": True, "no_solution_reveal": True, "feedback_style": "socratic", "max_questions": 2, "max_tokens": 500}
        },
        "summary": "A critique of the user's submission"
    }
])]) -> CritiqueResponse:
    return CritiqueService().critique(payload=payload)
