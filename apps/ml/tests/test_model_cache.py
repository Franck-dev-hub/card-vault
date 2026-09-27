from typing import TYPE_CHECKING

import faiss
import pytest

from app.models import model

if TYPE_CHECKING:
    from pathlib import Path


class _FakeIndex:
    ntotal = 1


def _pretend_index_sits_on_disk(
    monkeypatch: pytest.MonkeyPatch, tmp_path: Path
) -> None:
    # Fake a prebuilt index on disk and drop any cached one
    index_file = tmp_path / "cards_index.faiss"
    names_file = tmp_path / "cards_metadata.json"
    index_file.touch()
    names_file.touch()

    monkeypatch.setattr(model, "INDEX_FILE", index_file)
    monkeypatch.setattr(model, "NAMES_FILE", names_file)
    monkeypatch.setattr(model, "_load_metadata", lambda: [{"id": "x"}])
    monkeypatch.setattr(model, "_index", None)
    monkeypatch.setattr(model, "_metadata", None)


def test_the_index_is_read_once_and_then_reused(
    monkeypatch: pytest.MonkeyPatch, tmp_path: Path
) -> None:
    _pretend_index_sits_on_disk(monkeypatch, tmp_path)
    reads: list[str] = []

    def fake_read_index(path: str) -> _FakeIndex:
        reads.append(path)
        return _FakeIndex()

    monkeypatch.setattr(faiss, "read_index", fake_read_index)

    model._ensure_index()
    model._ensure_index()

    assert len(reads) == 1, "the index must not be reloaded per request"


def test_warm_up_leaves_a_missing_index_lazy(
    monkeypatch: pytest.MonkeyPatch, tmp_path: Path
) -> None:
    def must_not_run() -> None:
        pytest.fail("warm_up must not build the index")

    monkeypatch.setattr(model, "INDEX_FILE", tmp_path / "absent.faiss")
    monkeypatch.setattr(model, "NAMES_FILE", tmp_path / "absent.json")
    monkeypatch.setattr(model, "build_index", must_not_run)

    model.warm_up()
