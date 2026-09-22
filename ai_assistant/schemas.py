from pydantic import BaseModel, Field
from typing import Optional


class AskRequest(BaseModel):
    student_id: int = Field(..., gt=0, description="The student's user ID")
    course_id: int = Field(..., gt=0, description="The course ID")
    curriculum_id: Optional[int] = Field(None, gt=0, description="Specific topic/curriculum ID (optional)")
    question: str = Field(..., min_length=1, max_length=2000, description="Student's question")
    session_id: Optional[str] = Field(None, max_length=128, description="Chat session ID for conversation history")


class AskResponse(BaseModel):
    model_config = {"protected_namespaces": ()}

    answer: str
    course_title: str
    topic_title: Optional[str] = None
    model_used: str
    session_id: str


class HealthResponse(BaseModel):
    status: str
    service: str
