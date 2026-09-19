import app.models.model as model


class _FakeIndex:
    ntotal = 1


def _pretend_index_sits_on_disk(monkeypatch, tmp_path):
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


def test_the_index_is_read_once_and_then_reused(monkeypatch, tmp_path):
    _pretend_index_sits_on_disk(monkeypatch, tmp_path)
    reads = []

    def fake_read_index(path):
        reads.append(path)
        return _FakeIndex()

    monkeypatch.setattr(model.faiss, "read_index", fake_read_index)

    model._ensure_index()
    model._ensure_index()

    assert len(reads) == 1, "the index must not be reloaded per request"


def test_warm_up_leaves_a_missing_index_lazy(monkeypatch, tmp_path):
    def must_not_run():
        raise AssertionError("warm_up must not build the index")

    monkeypatch.setattr(model, "INDEX_FILE", tmp_path / "absent.faiss")
    monkeypatch.setattr(model, "NAMES_FILE", tmp_path / "absent.json")
    monkeypatch.setattr(model, "build_index", must_not_run)

    model.warm_up()
