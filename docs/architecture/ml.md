# Machine learning architecture

## Stack

Python 3.12, FastAPI, PyTorch, Hugging Face Transformers (DINOv2), FAISS.\
Dependencies are managed with uv (`pyproject.toml` + `uv.lock`) in the image
build.

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
| GET    | `/ml/api/v1/docs`    | OpenAPI documentation |

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

No training: DINOv2 is used as is, as an embedding model.

## Dependencies

Installed at build time in `docker/ml/Dockerfile`.\
The Hugging Face cache is a named volume; `HF_TOKEN` is required on first run to
download the model.
