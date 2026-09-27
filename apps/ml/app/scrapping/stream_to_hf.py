"""Stream card images to the Hugging Face dataset."""

import logging
import os
from http import HTTPStatus
from typing import TYPE_CHECKING, Any

import requests
from datasets import Dataset, Features, Image, Value
from huggingface_hub import login

from app.log import setup_logging

from . import pokemon_scrap as pokemon_manager

if TYPE_CHECKING:
    from collections.abc import Iterator

logger = logging.getLogger(__name__)

login(token=os.getenv("HF_TOKEN"))


def card_generator() -> Iterator[dict[str, Any]]:
    """Yield one dataset row per card whose image downloads."""
    logger.info("Generating the dataset")
    all_cards = pokemon_manager.fetch_all_cards()

    count = 0
    for card in all_cards:
        image_url = card.get("image")

        if image_url:
            if not image_url.endswith(".webp"):
                image_url = f"{image_url}/high.webp"

            try:
                res = requests.get(image_url, timeout=10)
                if res.status_code == HTTPStatus.OK:
                    yield {
                        "image": {"path": None, "bytes": res.content},
                        "name": card.get("name", "Unknown"),
                        "id_card": card.get("id", "Unknown"),
                    }
                    count += 1
                    if count % 100 == 0:
                        logger.info("Uploaded %d cards", count)
            except Exception:
                logger.exception("Failed to fetch image %s", image_url)
                continue


def create_dataset_streaming() -> None:
    """Build the dataset from the generator and push it to HF."""
    features = Features(
        {"image": Image(), "name": Value("string"), "id_card": Value("string")}
    )

    logger.info("Streaming to Hugging Face")
    ds = Dataset.from_generator(card_generator, features=features)

    ds.push_to_hub("Franck-dev/CardVault", embed_external_files=True)
    logger.info("Dataset online")


if __name__ == "__main__":
    setup_logging()
    create_dataset_streaming()
