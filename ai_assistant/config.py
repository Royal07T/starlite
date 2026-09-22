import os
import logging

from pydantic_settings import BaseSettings
from dotenv import load_dotenv

load_dotenv(os.path.join(os.path.dirname(__file__), ".env"))

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(name)s: %(message)s",
)
logger = logging.getLogger("ai_assistant")


class Settings(BaseSettings):
    DB_HOST: str = "127.0.0.1"
    DB_PORT: int = 3306
    DB_NAME: str = "***REMOVED***"
    DB_USER: str = "root"
    DB_PASSWORD: str = ""
    OPENAI_API_KEY: str = ""
    OPENAI_ORGANIZATION: str = ""
    OPENAI_MODEL: str = "gpt-4o-mini"
    OPENAI_TIMEOUT: int = 30
    OPENAI_MAX_TOKENS: int = 1500
    SESSION_MAX_AGE: int = 3600
    SESSION_MAX_HISTORY: int = 20
    RATE_LIMIT_PER_MINUTE: int = 20

    @property
    def DATABASE_URL(self) -> str:
        from urllib.parse import quote_plus
        password = quote_plus(self.DB_PASSWORD) if self.DB_PASSWORD else ""
        auth = f"{self.DB_USER}:{password}" if password else self.DB_USER
        return f"mysql+pymysql://{auth}@{self.DB_HOST}:{self.DB_PORT}/{self.DB_NAME}"

    class Config:
        env_file = os.path.join(os.path.dirname(__file__), ".env")
        extra = "ignore"


settings = Settings()
