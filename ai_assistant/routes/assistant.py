import time
import logging
from fastapi import APIRouter, Depends, HTTPException, Request
from sqlalchemy.orm import Session

from database import get_db
from schemas import AskRequest, AskResponse, HealthResponse
from services.course_context import verify_enrollment, get_course_context, build_system_prompt
from services.ai_service import chat
from config import settings

logger = logging.getLogger("ai_assistant")

router = APIRouter(prefix="/api/v1", tags=["assistant"])

rate_limit_store: dict[str, list[float]] = {}


def check_rate_limit(client_ip: str):
    now = time.time()
    window = 60
    if client_ip not in rate_limit_store:
        rate_limit_store[client_ip] = []
    rate_limit_store[client_ip] = [
        t for t in rate_limit_store[client_ip] if now - t < window
    ]
    if len(rate_limit_store[client_ip]) >= settings.RATE_LIMIT_PER_MINUTE:
        raise HTTPException(
            status_code=429,
            detail="Rate limit exceeded. Please wait before trying again.",
        )
    rate_limit_store[client_ip].append(now)


@router.get("/health", response_model=HealthResponse)
def health_check():
    return HealthResponse(status="ok", service="ai-assistant")


@router.post("/ask", response_model=AskResponse)
def ask_question(req: AskRequest, request: Request, db: Session = Depends(get_db)):
    client_ip = request.client.host if request.client else "unknown"
    check_rate_limit(client_ip)

    if not verify_enrollment(db, req.student_id, req.course_id):
        raise HTTPException(status_code=403, detail="You are not enrolled in this course")

    context = get_course_context(db, req.course_id, req.curriculum_id)
    if not context:
        raise HTTPException(status_code=404, detail="Course not found")

    if not settings.OPENAI_API_KEY:
        raise HTTPException(status_code=500, detail="AI service is not configured")

    try:
        answer, session_id, _ = chat(
            session_id=req.session_id,
            system_prompt=build_system_prompt(context),
            question=req.question,
        )
    except HTTPException:
        raise
    except Exception:
        logger.exception("OpenAI request failed for student=%d course=%d", req.student_id, req.course_id)
        raise HTTPException(status_code=502, detail="AI service is temporarily unavailable")

    return AskResponse(
        answer=answer,
        course_title=context["course_title"],
        topic_title=context.get("active_topic"),
        model_used=settings.OPENAI_MODEL,
        session_id=session_id,
    )
