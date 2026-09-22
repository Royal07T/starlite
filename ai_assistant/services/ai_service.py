import time
import uuid
import logging
from openai import OpenAI
from config import settings

logger = logging.getLogger("ai_assistant")

client = None
sessions: dict[str, dict] = {}


def get_client() -> OpenAI:
    global client
    if client is None:
        client = OpenAI(
            api_key=settings.OPENAI_API_KEY or None,
            organization=settings.OPENAI_ORGANIZATION or None,
            timeout=settings.OPENAI_TIMEOUT,
        )
    return client


def _cleanup_expired_sessions():
    now = time.time()
    expired = [
        sid for sid, data in sessions.items()
        if now - data["last_active"] > settings.SESSION_MAX_AGE
    ]
    for sid in expired:
        del sessions[sid]
    if expired:
        logger.info("Cleaned up %d expired sessions", len(expired))


def get_or_create_session(session_id: str | None, system_prompt: str) -> tuple[str, list[dict]]:
    _cleanup_expired_sessions()

    if session_id and session_id in sessions:
        sessions[session_id]["last_active"] = time.time()
        return session_id, sessions[session_id]["messages"]

    new_id = session_id or str(uuid.uuid4())
    messages = [{"role": "system", "content": system_prompt}]
    sessions[new_id] = {
        "messages": messages,
        "last_active": time.time(),
    }
    return new_id, messages


def trim_history(messages: list[dict]) -> list[dict]:
    max_hist = settings.SESSION_MAX_HISTORY
    if len(messages) <= max_hist:
        return messages
    system = messages[:1]
    recent = messages[-(max_hist - 1):]
    return system + recent


def count_tokens_approx(text: str) -> int:
    return len(text) // 4


def chat(session_id: str | None, system_prompt: str, question: str) -> tuple[str, str, list[dict]]:
    sid, messages = get_or_create_session(session_id, system_prompt)

    sys_tokens = count_tokens_approx(system_prompt)
    hist_tokens = sum(count_tokens_approx(m["content"]) for m in messages[1:])
    q_tokens = count_tokens_approx(question)
    total = sys_tokens + hist_tokens + q_tokens

    if total > 120000:
        logger.warning("Prompt too large (%d tokens approx), trimming history aggressively", total)
        messages = [messages[0]]
        sessions[sid]["messages"] = messages

    messages.append({"role": "user", "content": question})
    messages = trim_history(messages)
    sessions[sid]["messages"] = messages

    openai_client = get_client()
    response = openai_client.chat.completions.create(
        model=settings.OPENAI_MODEL,
        messages=messages,
        max_tokens=settings.OPENAI_MAX_TOKENS,
        temperature=0.7,
    )

    answer = response.choices[0].message.content.strip()
    messages.append({"role": "assistant", "content": answer})
    sessions[sid]["messages"] = messages
    sessions[sid]["last_active"] = time.time()

    logger.info(
        "session=%s model=%s prompt_tokens=%d completion_tokens=%d",
        sid[:8],
        response.model,
        response.usage.prompt_tokens if response.usage else 0,
        response.usage.completion_tokens if response.usage else 0,
    )

    return answer, sid, messages
