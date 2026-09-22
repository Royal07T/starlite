from fastapi import APIRouter, Depends, HTTPException
from sqlalchemy.orm import Session

from database import get_db
from schemas import AskRequest, AskResponse, HealthResponse
from services.course_context import verify_enrollment, get_course_context, build_system_prompt
from services.ai_service import chat
from config import settings

router = APIRouter(prefix="/api/v1", tags=["assistant"])


@router.get("/health", response_model=HealthResponse)
def health_check():
    return HealthResponse(status="ok", service="ai-assistant")


@router.post("/ask", response_model=AskResponse)
def ask_question(req: AskRequest, db: Session = Depends(get_db)):
    if not verify_enrollment(db, req.student_id, req.course_id):
        raise HTTPException(status_code=403, detail="You are not enrolled in this course")

    context = get_course_context(db, req.course_id, req.curriculum_id)
    if not context:
        raise HTTPException(status_code=404, detail="Course not found")

    if not settings.OPENAI_API_KEY:
        raise HTTPException(status_code=500, detail="OpenAI API key not configured")

    system_prompt = build_system_prompt(context)

    try:
        answer, session_id, _ = chat(
            session_id=req.session_id,
            system_prompt=system_prompt,
            question=req.question,
        )
    except Exception as e:
        raise HTTPException(status_code=502, detail=f"AI service error: {str(e)}")

    return AskResponse(
        answer=answer,
        course_title=context["course_title"],
        topic_title=context.get("active_topic"),
        model_used="gpt-4o-mini",
        session_id=session_id,
    )
