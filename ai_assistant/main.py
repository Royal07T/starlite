import sys
import os

sys.path.insert(0, os.path.dirname(__file__))

import uvicorn
from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware

from routes.assistant import router as assistant_router

app = FastAPI(
    title="Starlite AI Assistant",
    description="AI-powered course tutor assistant for Starlite platform",
    version="1.0.0",
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=[
        "http://127.0.0.1:8000",
        "http://localhost:8000",
    ],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

app.include_router(assistant_router)


@app.get("/")
def root():
    return {"message": "Starlite AI Assistant is running", "docs": "/docs"}


if __name__ == "__main__":
    uvicorn.run("main:app", host="127.0.0.1", port=8001, reload=True)
