from fastapi import FastAPI
from src.api import router as coach_router

app = FastAPI()


app.include_router(coach_router)

@app.get("/")
def read_root():
    return {"Hello": "World"}


@app.get("/items/{item_id}")
def read_item(item_id: int, q: str | None = None):
    return {"item_id": item_id, "q": q}