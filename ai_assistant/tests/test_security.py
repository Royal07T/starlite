"""AuthN/authZ tests for the AI assistant /ask endpoint (Phase 1 security fixes)."""

import os
from typing import Generator

os.environ.setdefault("AI_SERVICE_TOKEN", "test-service-token")

import pytest
from fastapi.testclient import TestClient

import config
from main import app
from routes import assistant


@pytest.fixture()
def client() -> Generator[TestClient, None, None]:
    with TestClient(app) as test_client:
        yield test_client


@pytest.fixture()
def override_db():
    """Stub the enrollment verifier and course context so the auth path can be
    exercised without a database or upstream RAG service.
    """
    original_enroll = assistant.verify_enrollment
    assistant.verify_enrollment = lambda db, student_id, course_id: True
    original_context = assistant.get_course_context
    assistant.get_course_context = lambda db, course_id, curriculum_id: {
        "course_title": "Test",
        "active_topic": None,
        "chunks": [],
    }
    yield
    assistant.verify_enrollment = original_enroll
    assistant.get_course_context = original_context


def test_ask_requires_bearer_token(client: TestClient):
    resp = client.post(
        "/api/v1/ask",
        json={"student_id": 1, "course_id": 2, "question": "what?"},
    )
    assert resp.status_code == 401


def test_ask_rejects_bad_token(client: TestClient):
    resp = client.post(
        "/api/v1/ask",
        headers={"Authorization": "Bearer wrong-token"},
        json={"student_id": 1, "course_id": 2, "question": "what?"},
    )
    assert resp.status_code == 401


def test_ask_accepts_valid_token(client: TestClient, override_db):
    resp = client.post(
        "/api/v1/ask",
        headers={"Authorization": f"Bearer {config.settings.AI_SERVICE_TOKEN}"},
        json={"student_id": 1, "course_id": 2, "question": "what?"},
    )
    # Auth passed; without an OpenAI key it must fail closed with 5xx, never 200.
    assert resp.status_code in (500, 502)