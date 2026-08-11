import logging
import requests
import os
from . import pokemon_scrap as pokemon_manager

# from . import magic_scrap as magic_manager
from datasets import Dataset, Features, Image, Value
from huggingface_hub import login

logger = logging.getLogger(__name__)

login(token=os.getenv("HF_TOKEN"))


def card_generator():
    print("Generate dataset")
    pokemon_cards = pokemon_manager.fetch_all_cards()
    # magic_cards = magic_manager.fetch_all_cards()
    all_cards = pokemon_cards  # + magic_cards

    count = 0
    for card in all_cards:
        image_url = card.get("image")

        if image_url:
            if not image_url.endswith(".webp"):
                image_url = f"{image_url}/high.webp"

            try:
                res = requests.get(image_url, timeout=10)
                if res.status_code == 200:
                    yield {
                        "image": {"path": None, "bytes": res.content},
                        "name": card.get("name", "Unknown"),
                        "id_card": card.get("id", "Unknown"),
                    }
                    count += 1
                    if count % 100 == 0:
                        print(f"Total upload : {count} cards", end="\r")
            except Exception:
                logger.exception("Failed to fetch image %s", image_url)
                continue


def create_dataset_streaming():
    features = Features(
        {"image": Image(), "name": Value("string"), "id_card": Value("string")}
    )

    print("Streaming to Hugging Face")
    ds = Dataset.from_generator(card_generator, features=features)

    ds.push_to_hub("Franck-dev/CardVault", embed_external_files=True)
    print("\nDataset online.")


if __name__ == "__main__":
    create_dataset_streaming()
