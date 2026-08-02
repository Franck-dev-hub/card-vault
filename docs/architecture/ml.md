# Machine learning architecture

## Stack

Python 3.12, FastAPI, PyTorch, Hugging Face Transformers (DINOv2), FAISS.
Dependencies managed with pip in the image build.

## Role

Card image classification: given a photo of a card, return the best matching
cards. The service is internal, proxied by the backend at `/api/scan`, never
exposed to the browser.

## Training vs inference

Not implemented yet.

- Training: dataset of card images, offline pipeline, produces the FAISS
  index.
- Inference: the DINOv2 model and FAISS index are loaded once at startup and
  kept in memory. Handlers are `def` (FastAPI threadpool), never blocking async
  handlers for CPU-bound inference.

## Dependencies

Requirements installed at build time in `docker/ml/Dockerfile`. The Hugging
Face cache is a named volume; `HF_TOKEN` is required on first run to download
the model.
