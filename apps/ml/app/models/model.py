"""DINOv2 embeddings and the FAISS index of known cards."""

import io
import json
import logging
import os
import threading
from pathlib import Path
from typing import Any

import faiss
import numpy as np
import requests
import torch
from datasets import load_dataset
from huggingface_hub import hf_hub_download
from PIL import Image
from transformers import AutoImageProcessor, AutoModel

# Configuration
BASE_DIR = Path(__file__).parent.absolute()
MODEL_NAME = "facebook/dinov2-small"
HF_DATASET_ID = "Franck-dev/CardVault"
DATA_DIR = BASE_DIR / "data_cache"
INDEX_FILE = DATA_DIR / "cards_index.faiss"
NAMES_FILE = DATA_DIR / "cards_metadata.json"
BATCH_SIZE = 128
BACKEND_URL = os.getenv("BACKEND_URL", "http://api:8000")

type CardMatch = dict[str, Any]
type CardEntry = dict[str, str]

logger = logging.getLogger(__name__)

device = torch.device("cuda" if torch.cuda.is_available() else "cpu")

logger.info("Loading %s on %s", MODEL_NAME, device)
processor = AutoImageProcessor.from_pretrained(MODEL_NAME, use_fast=True)
model = AutoModel.from_pretrained(MODEL_NAME).to(device)
model.eval()


def build_index() -> None:
    """Download the index from HF, or build it from the dataset."""
    try:
        logger.info("Looking for the index on HF")
        hf_hub_download(
            repo_id=HF_DATASET_ID,
            filename="cards_index.faiss",
            repo_type="dataset",
            local_dir=str(DATA_DIR),
        )
        hf_hub_download(
            repo_id=HF_DATASET_ID,
            filename="cards_metadata.json",
            repo_type="dataset",
            local_dir=str(DATA_DIR),
        )
    except Exception:
        logger.warning("No index on HF, building a local one", exc_info=True)
    else:
        logger.info("Index loaded from HF")
        return

    # Fallback if construction not found
    ds = load_dataset(HF_DATASET_ID, split="train", streaming=True)

    # Fetch dimension dynamicly
    index = faiss.IndexFlatIP(model.config.hidden_size)
    metadata: list[CardEntry] = []
    processed_count = 0

    logger.info("Indexing cards")
    for batch in ds.iter(batch_size=BATCH_SIZE):
        try:
            images = [img.convert("RGB") for img in batch["image"]]
            inputs = processor(images=images, return_tensors="pt").to(device)

            with torch.no_grad():
                outputs = model(**inputs)
                embeddings = (
                    outputs.last_hidden_state[:, 0, :].float().cpu().numpy()
                )

            embeddings = np.ascontiguousarray(embeddings.astype("float32"))
            faiss.normalize_L2(embeddings)
            index.add(embeddings)

            # Store Name + ID
            for name, id_card in zip(
                batch["name"], batch["id_card"], strict=True
            ):
                metadata.append({"name": name, "id": id_card})

            processed_count += len(images)
            logger.debug("Indexed cards: %d", processed_count)

        except Exception:
            logger.exception("Batch failed")
            continue

    if index.ntotal == 0 or len(metadata) == 0:
        logger.error("Empty index, check the HF dataset")
        return

    logger.info("Saving %d cards in %s", index.ntotal, DATA_DIR)
    DATA_DIR.mkdir(parents=True, exist_ok=True)
    faiss.write_index(index, str(INDEX_FILE))
    with NAMES_FILE.open("w", encoding="utf-8") as f:
        json.dump(metadata, f)


def _load_metadata() -> list[CardEntry]:
    with NAMES_FILE.open(encoding="utf-8") as f:
        data = json.load(f)
    if not isinstance(data, list):
        msg = "cards_metadata.json must contain a JSON array"
        raise TypeError(msg)
    for entry in data:
        if not isinstance(entry, dict) or not isinstance(entry.get("id"), str):
            msg = "cards_metadata.json entry missing string id"
            raise TypeError(msg)
    return data


_index: faiss.Index | None = None
_metadata: list[CardEntry] | None = None
_index_lock = threading.Lock()


def _ensure_index() -> tuple[faiss.Index, list[CardEntry]]:
    global _index, _metadata

    if _index is not None and _metadata is not None:
        return _index, _metadata

    with _index_lock:
        if _index is not None and _metadata is not None:
            return _index, _metadata

        if not INDEX_FILE.exists() or not NAMES_FILE.exists():
            build_index()

        index = faiss.read_index(str(INDEX_FILE))
        metadata = _load_metadata()

        if index.ntotal == 0 or len(metadata) == 0:
            logger.warning("Empty local index, rebuilding")
            build_index()
            index = faiss.read_index(str(INDEX_FILE))
            metadata = _load_metadata()

        _index, _metadata = index, metadata

        return index, metadata


def warm_up() -> None:
    """Load the index at startup when it is already on disk."""
    if INDEX_FILE.exists() and NAMES_FILE.exists():
        _ensure_index()


def search_card(image_bytes: bytes) -> list[CardMatch]:
    """Return the three closest cards, with their data from the API."""
    index, metadata = _ensure_index()

    query_img = Image.open(io.BytesIO(image_bytes)).convert("RGB")
    inputs = processor(images=query_img, return_tensors="pt").to(device)

    with torch.no_grad():
        outputs = model(**inputs)
        query_emb = outputs.last_hidden_state[:, 0, :].cpu().numpy()

    faiss.normalize_L2(query_emb)

    # Get 3 best matches
    scores, indices = index.search(query_emb, 3)
    results: list[CardMatch] = []

    for i in range(3):
        idx = indices[0][i]
        if idx == -1:
            continue

        score = scores[0][i]
        card_id = metadata[idx]["id"]
        url = f"{BACKEND_URL}/api/games/pokemon/cards/pokemon-{card_id}"

        try:
            response = requests.get(url, timeout=2.0)
            response.raise_for_status()
            results.append(
                {"score": round(float(score), 4), "data": response.json()}
            )
        except requests.exceptions.RequestException as e:
            logger.warning("API error for card %s: %s", card_id, e)
            results.append(
                {
                    "score": round(float(score), 4),
                    "card_id": card_id,
                    "error": "API Unreachable",
                }
            )

    return results
