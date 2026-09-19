import io
import json
import os
import threading
import faiss
import requests
import torch
import numpy as np
from pathlib import Path
from PIL import Image
from fastapi import HTTPException
from transformers import AutoImageProcessor, AutoModel
from datasets import load_dataset
from huggingface_hub import hf_hub_download

# Configuration
BASE_DIR = Path(__file__).parent.absolute()
MODEL_NAME = "facebook/dinov2-small"
HF_DATASET_ID = "Franck-dev/CardVault"
DATA_DIR = BASE_DIR / "data_cache"
INDEX_FILE = DATA_DIR / "cards_index.faiss"
NAMES_FILE = DATA_DIR / "cards_metadata.json"
BATCH_SIZE = 128
BACKEND_URL = os.getenv("BACKEND_URL", "http://localhost:8000")

device = torch.device("cuda" if torch.cuda.is_available() else "cpu")

print(f"Loading {MODEL_NAME} on {device}")
processor = AutoImageProcessor.from_pretrained(  # nosec B615
    MODEL_NAME, use_fast=True
)
model = AutoModel.from_pretrained(MODEL_NAME).to(device)  # nosec B615
model.eval()


def build_index():
    try:
        print("Research index on HF")
        hf_hub_download(  # nosec B615
            repo_id=HF_DATASET_ID,
            filename="cards_index.faiss",
            repo_type="dataset",
            local_dir=str(DATA_DIR),
        )
        hf_hub_download(  # nosec B615
            repo_id=HF_DATASET_ID,
            filename="cards_metadata.json",
            repo_type="dataset",
            local_dir=str(DATA_DIR),
        )
        print("Index loaded from HF")
        return
    except Exception as e:
        print(f"No index found online ({e}). Building local index")

    # Fallback if construction not found
    ds = load_dataset(  # nosec B615
        HF_DATASET_ID, split="train", streaming=True
    )

    # Fetch dimension dynamicly
    index = faiss.IndexFlatIP(model.config.hidden_size)
    metadata = []
    processed_count = 0

    print("Indexing cards")
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
            for name, id_card in zip(batch["name"], batch["id_card"]):
                metadata.append({"name": name, "id": id_card})

            processed_count += len(images)
            print(f"Indexed cards : {processed_count}", end="\r")

        except Exception as e:
            print(f"\nBatch error : {e}")
            continue

    if index.ntotal == 0 or len(metadata) == 0:
        print("\nError : Empty index. Check HF dataset")
        return

    print(f"\nSaving index in : {DATA_DIR}")
    os.makedirs(str(DATA_DIR), exist_ok=True)
    faiss.write_index(index, str(INDEX_FILE))
    with open(str(NAMES_FILE), "w", encoding="utf-8") as f:
        json.dump(metadata, f)


def _load_metadata() -> list:
    with open(str(NAMES_FILE), encoding="utf-8") as f:
        data = json.load(f)
    if not isinstance(data, list):
        raise ValueError("cards_metadata.json must contain a JSON array")
    for entry in data:
        if not isinstance(entry, dict) or not isinstance(
            entry.get("id"), str
        ):
            raise ValueError("cards_metadata.json entry missing string id")
    return data


_index: faiss.Index | None = None
_metadata: list | None = None
_index_lock = threading.Lock()


def _ensure_index() -> tuple[faiss.Index, list]:
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
            print("Empty index detected locally. Rebuilding...")
            build_index()
            index = faiss.read_index(str(INDEX_FILE))
            metadata = _load_metadata()

        _index, _metadata = index, metadata

        return index, metadata


def warm_up() -> None:
    if INDEX_FILE.exists() and NAMES_FILE.exists():
        _ensure_index()


def search_card(image_bytes: bytes):
    try:
        index, metadata = _ensure_index()

        # Prepare image to test
        query_img = Image.open(io.BytesIO(image_bytes)).convert("RGB")
        inputs = processor(images=query_img, return_tensors="pt").to(device)

        with torch.no_grad():
            outputs = model(**inputs)
            query_emb = outputs.last_hidden_state[:, 0, :].cpu().numpy()

        faiss.normalize_L2(query_emb)

        # Get 3 best matches
        scores, indices = index.search(query_emb, 3)
        results = []

        for i in range(3):
            idx = indices[0][i]
            if idx == -1:
                print(f"#{i + 1} : No match found.")
                continue
            score = scores[0][i]
            card_id = metadata[idx]["id"]

            formatted_id = card_id.replace("-", "/")
            url = f"http://backend:8000/api/search/pokemon/{formatted_id}"
            try:
                response = requests.get(url, timeout=2.0)
                response.raise_for_status()
                api_data = response.json()

                results.append(
                    {"score": round(float(score), 4), "data": api_data}
                )

            except requests.exceptions.RequestException as e:
                print(f"API error for ID {card_id}: {e}")
                results.append(
                    {
                        "score": float(score),
                        "card_id": card_id,
                        "error": "API Unreachable",
                    }
                )

        return results

    except Exception as e:
        print(f"Search error: {type(e).__name__}: {e}")
        import traceback

        traceback.print_exc()
        raise HTTPException(status_code=500, detail=str(e))
