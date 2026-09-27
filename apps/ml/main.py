"""FastAPI entry point of the ML service."""

from contextlib import asynccontextmanager
from typing import TYPE_CHECKING

from fastapi import FastAPI

from app.log import setup_logging

if TYPE_CHECKING:
    from collections.abc import AsyncIterator

# Before the app imports: the model logs while it loads at import time.
setup_logging()

from app.api.v1.predict import router as predict_router  # noqa: E402
from app.models.model import warm_up  # noqa: E402


@asynccontextmanager
async def lifespan(_app: FastAPI) -> AsyncIterator[None]:
    """Load the index before the first request."""
    warm_up()
    yield


def create_app() -> FastAPI:
    """Build the app with its routes and health check."""
    app = FastAPI(
        title="Card Vault Model",
        description="Card Vault Model API",
        version="1.0.0",
        docs_url="/api/v1/docs",
        redoc_url=None,
        lifespan=lifespan,
    )

    app.include_router(predict_router, prefix="/ml/api/v1")

    @app.get("/ml/health")
    async def health() -> dict[str, str]:
        return {"status": "ok"}

    return app


app = create_app()
