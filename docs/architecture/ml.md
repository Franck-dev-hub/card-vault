# Machine learning architecture

## Stack

Python 3.12, FastAPI, PyTorch, Hugging Face Transformers (DINOv2), FAISS.
Dependencies managed with uv (`pyproject.toml` + `uv.lock`) in the image build.

## Role

Card image classification: given a photo of a card, return the best matching
cards. The service is internal: it publishes no port and the public proxy
answers 404 on `/ml/*`. Only the backend calls it, at `http://ml:5000` on the
Docker network; the `/api/scan` endpoint that will do so is tracked in #16.

## Training vs inference

- Training: not implemented yet. Dataset of card images, offline pipeline,
  produces the FAISS index.
- Inference: live. The DINOv2 model and FAISS index are loaded once at startup
  and kept in memory. Handlers are `def` (FastAPI threadpool), never blocking
  async handlers for CPU-bound inference.

## Dependencies

Requirements installed at build time in `docker/ml/Dockerfile`. The Hugging
Face cache is a named volume; `HF_TOKEN` is required on first run to download
the model.
