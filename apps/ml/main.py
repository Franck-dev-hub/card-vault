from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from app.api.v1.predict import router as predict_router


def create_app() -> FastAPI:
    app = FastAPI(
        title="Card Vault Model",
        description="Card Vault Model API",
        version="1.0.0",
        docs_url="/api/v1/docs",
        redoc_url=None,
    )

    # CORS Configuration
    app.add_middleware(
        CORSMiddleware,
        allow_origins=["http://localhost:3000"],
        allow_credentials=True,
        allow_methods=["GET", "POST", "PUT", "DELETE", "OPTIONS"],
        allow_headers=["Content-Type", "Authorization"],
    )

    # Include Routers
    app.include_router(predict_router, prefix="/ml/api/v1")

    @app.get("/ml/health")
    async def health() -> dict:
        return {"status": "ok"}

    return app


app = create_app()
