from fastapi.testclient import TestClient

from main import app


def test_preflight_gets_no_cors_headers() -> None:
    # No context manager: the lifespan would load the model.
    response = TestClient(app).options(
        "/ml/health",
        headers={
            "Origin": "http://card-vault.localhost",
            "Access-Control-Request-Method": "GET",
        },
    )

    headers = [h.lower() for h in response.headers]
    cors = [h for h in headers if h.startswith("access-control-")]
    assert cors == []
