from main import app


def test_health_route_registered() -> None:
    paths = app.openapi()["paths"]
    assert "/ml/health" in paths
    assert "get" in paths["/ml/health"]
