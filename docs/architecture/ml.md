# Machine learning architecture

## Stack

Python, FastAPI, PyTorch, Hugging Face Transformers (DINOv2), FAISS.\
Dependencies are managed with uv (`pyproject.toml` + `uv.lock`) in the image
build.\
Dev tools (ruff, mypy, pytest, pip-audit) live in the `dev` dependency group.\
The `ml_release` image has no dev tools, no uv binary and no extra apt
packages.\
The `ml_dev` image adds uv and the dev group; every build names its target.

## CPU and GPU

Torch and torchvision are not base dependencies: they come with the `cpu` or
`gpu` extra, which conflict.\
`cpu` takes the PyTorch CPU index wheels, with no NVIDIA library.\
`gpu` takes the PyPI wheels, built for CUDA 13.\
Both images and the CI sync `--extra cpu`: the release image drops from 9.4 GB
to 1.8 GB.\
A `uv sync` without an extra removes torch; a plain `uv run` keeps it.

To run the embeddings on the workstation GPU, sync a host venv:

```sh
cd apps/ml
uv sync --frozen --extra gpu
uv run --no-sync python -c "import torch; print(torch.cuda.is_available())"
```

It prints `True` with an NVIDIA driver that supports CUDA 13.

pip-audit skips local versions such as `2.14.1+cpu`: the image's torch and
torchvision are not audited.\
Renovate's OSV alerts still cover them, on the Mend portal only.

## Role

Card recognition: given a photo of a card, find the closest cards in the
catalogue.\
The service is internal: it publishes no port and the public proxy answers 404
on `/ml/*`.\
Only the backend calls it, at `http://ml:5000` on the Docker network; the
`/api/scan` endpoint that will do so is tracked in #16.

## Endpoints

| Method | Path                 | Purpose               |
|--------|----------------------|-----------------------|
| GET    | `/ml/health`         | Liveness check        |
| POST   | `/ml/api/v1/predict` | Match a card image    |
| GET    | `/api/v1/docs`       | OpenAPI documentation |

`predict` takes `{"image": "<base64>"}`, a data URL prefix is accepted, 2 MB
max.\
It answers 400 on invalid base64, otherwise the 3 best matches:

```json
[
  {
    "score": 0.9312,
    "data": {
      "...": "Card, as returned by the API"
    }
  }
]
```

Each match is enriched by calling the API single card route (`BACKEND_URL`,
default `http://api:8000`).\
When that call fails, the match carries `card_id` and
`"error": "API Unreachable"` instead of `data`.\
Pokémon only for now.

## Data pipeline

1. `app/scrapping/` downloads card images and pushes them as the Hugging Face
   dataset `Franck-dev/CardVault` (`stream_to_hf.py`).
2. The service loads the FAISS index and metadata from its local cache at
   startup.\
   If absent, the first request downloads them prebuilt from that dataset, or
   builds them locally from the images.
3. Inference: the DINOv2 model and FAISS index are loaded once and kept in
   memory.\
   Handlers are `def` (FastAPI threadpool), never blocking async handlers for
   CPU-bound inference.

No training: DINOv2 is used as is, as an embedding model, see
[ADR 0002](../adr/0002-card-recognition-by-similarity.md).
