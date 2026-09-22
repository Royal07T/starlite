import logging
from sqlalchemy.orm import Session
from models import Course, Section, Curriculum, Faq, Enrollment

logger = logging.getLogger("ai_assistant")

MAX_ARTICLE_CHARS = 4000
MAX_DESCRIPTION_CHARS = 1000


def verify_enrollment(db: Session, student_id: int, course_id: int) -> bool:
    enrollment = (
        db.query(Enrollment)
        .filter(
            Enrollment.student_id == student_id,
            Enrollment.course_id == course_id,
            Enrollment.status == 1,
        )
        .first()
    )
    return enrollment is not None


def get_course_context(
    db: Session, course_id: int, curriculum_id: int | None = None
) -> dict | None:
    course = db.query(Course).filter(Course.id == course_id).first()
    if not course:
        return None

    context = {
        "course_title": course.title,
        "course_description": (course.description or "")[:MAX_DESCRIPTION_CHARS],
        "learning_objectives": course.learning_objectives or [],
        "prerequisites": course.prerequisites or "",
        "sections": [],
        "faqs": [],
    }

    for faq in course.faqs[:10]:
        context["faqs"].append({"question": faq.question, "answer": faq.answer})

    if curriculum_id:
        target_curriculum = (
            db.query(Curriculum).filter(Curriculum.id == curriculum_id).first()
        )
        if target_curriculum:
            section = target_curriculum.section
            context["sections"].append(
                {
                    "section_title": section.title,
                    "curriculums": [
                        {
                            "id": target_curriculum.id,
                            "title": target_curriculum.title,
                            "description": (target_curriculum.description or "")[:MAX_DESCRIPTION_CHARS],
                            "article_content": (target_curriculum.article_content or "")[:MAX_ARTICLE_CHARS],
                            "type": target_curriculum.type,
                        }
                    ],
                }
            )
            context["active_topic"] = target_curriculum.title
    else:
        for section in course.sections[:10]:
            section_data = {
                "section_title": section.title,
                "curriculums": [],
            }
            for curr in section.curriculums[:20]:
                section_data["curriculums"].append(
                    {
                        "id": curr.id,
                        "title": curr.title,
                        "description": (curr.description or "")[:MAX_DESCRIPTION_CHARS],
                        "type": curr.type,
                    }
                )
            context["sections"].append(section_data)

    return context


def build_system_prompt(context: dict) -> str:
    parts = [
        "You are an AI tutor helping a student understand their course material.",
        "Answer questions clearly, accurately, and in a way that promotes understanding.",
        "Use simple language and provide examples when helpful.",
        "If the student asks something outside the course content, politely redirect them to the course material.",
        "",
        f"COURSE: {context['course_title']}",
        f"DESCRIPTION: {context['course_description']}",
    ]

    if context.get("learning_objectives"):
        objectives = ", ".join(context["learning_objectives"][:5])
        parts.append(f"LEARNING OBJECTIVES: {objectives}")

    if context.get("prerequisites"):
        parts.append(f"PREREQUISITES: {context['prerequisites'][:500]}")

    if context.get("active_topic"):
        parts.append(f"\nThe student is currently viewing: {context['active_topic']}")

    if context.get("sections"):
        parts.append("\n--- COURSE CONTENT STRUCTURE ---")
        for section in context["sections"]:
            parts.append(f"\nSection: {section['section_title']}")
            for curr in section["curriculums"]:
                parts.append(f"  - {curr['title']} ({curr['type']})")
                if curr.get("article_content"):
                    parts.append(f"    Content: {curr['article_content']}")
                if curr.get("description"):
                    parts.append(f"    Description: {curr['description']}")

    if context.get("faqs"):
        parts.append("\n--- FREQUENTLY ASKED QUESTIONS ---")
        for faq in context["faqs"]:
            parts.append(f"Q: {faq['question']}")
            parts.append(f"A: {faq['answer']}")
            parts.append("")

    parts.append("\n--- END OF COURSE CONTEXT ---")
    parts.append("\nNow answer the student's question based on the above course content.")

    return "\n".join(parts)
