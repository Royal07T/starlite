from pydantic_settings import BaseSettings
from dotenv import load_dotenv
import os

load_dotenv(os.path.join(os.path.dirname(__file__), ".env"))


class Settings(BaseSettings):
    DB_HOST: str = "127.0.0.1"
    DB_PORT: int = 3306
    DB_NAME: str = "starlite_db"
    DB_USER: str = "root"
    DB_PASSWORD: str = ""
    OPENAI_API_KEY: str = ""
    OPENAI_ORGANIZATION: str = ""

    @property
    def DATABASE_URL(self) -> str:
        return (
            f"mysql+pymysql://{self.DB_USER}:{self.DB_PASSWORD}"
            f"@{self.DB_HOST}:{self.DB_PORT}/{self.DB_NAME}"
        )

    class Config:
        env_file = os.path.join(os.path.dirname(__file__), ".env")
        extra = "ignore"


settings = Settings()
