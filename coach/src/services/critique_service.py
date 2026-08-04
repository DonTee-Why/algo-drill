from src.schemas.critique import CritiquePayload, CritiqueResponse, Stage


def critique(payload: CritiquePayload) -> CritiqueResponse:
    # TODO: Build prompt
    # TODO: Call LLM
    # TODO: Parse response
    # TODO: Sanitize response
    # TODO: Return response
    return CritiqueResponse(
        coach_msg="Your clarification is detailed enough. Please provide more detail.",
        scores={
            "inputs_outputs": 1,
            "constraints": 1,
            "examples": 1,
            "total": 3,
        },
        flags={
            "inputs_outputs": True,
            "constraints": True,
            "examples": True,
        },
        questions=["What is the input format?", "What is the output format?"],
    )


def build_prompt(payload: CritiquePayload, stage: Stage) -> str:
    return f"""
    You are a coach for a coding interview.
    You are given a problem and a user's submission.
    You need to critique the user's submission.
    """
