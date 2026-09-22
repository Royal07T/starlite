import uuid
from openai import OpenAI
from config import settings

client = None
sessions: dict[str, list[dict]] = {}

MODEL = "gpt-4o-mini"
MAX_HISTORY = 20


def get_client() -> OpenAI:
    global client
    if client is None:
        client = OpenAI(
            api_key=settings.OPENAI_API_KEY,
            organization=settings.OPENAI_ORGANIZATION or None,
        )
    return client


def get_or_create_session(session_id: str | None, system_prompt: str) -> tuple[str, list[dict]]:
    if session_id and session_id in sessions:
        return session_id, sessions[session_id]

    new_id = session_id or str(uuid.uuid4())
    messages = [{"role": "system", "content": system_prompt}]
    sessions[new_id] = messages
    return new_id, messages


def trim_history(messages: list[dict]) -> list[dict]:
    if len(messages) <= MAX_HISTORY:
        return messages
    system = messages[:1]
    recent = messages[-(MAX_HISTORY - 1):]
    return system + recent


def chat(session_id: str | None, system_prompt: str, question: str) -> tuple[str, str, list[dict]]:
    sid, messages = get_or_create_session(session_id, system_prompt)
    messages.append({"role": "user", "content": question})
    messages = trim_history(messages)
    sessions[sid] = messages

    openai_client = get_client()
    response = openai_client.chat.completions.create(
        model=MODEL,
        messages=messages,
        max_tokens=1500,
        temperature=0.7,
    )

    answer = response.choices[0].message.content.strip()
    messages.append({"role": "assistant", "content": answer})
    sessions[sid] = messages

    return answer, sid, messages
