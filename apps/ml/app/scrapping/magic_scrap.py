"""Magic card images from Scryfall."""

import logging
import re
from io import BytesIO
from typing import TYPE_CHECKING, Any

import requests
from PIL import Image

if TYPE_CHECKING:
    from pathlib import Path

# Scryfall Configuration
BULK_INFO_URL = "https://api.scryfall.com/bulk-data"
TIMEOUT = 60
HEADERS = {"User-Agent": "CardVaultScrap/1.0"}

logger = logging.getLogger(__name__)


def fetch_all_cards() -> list[dict[str, Any]]:
    """Return every Scryfall card, or an empty list on failure."""
    logger.info("Requesting data from Scryfall")
    try:
        # Get the download URL
        response = requests.get(
            BULK_INFO_URL, headers=HEADERS, timeout=TIMEOUT
        )
        response.raise_for_status()
        bulk_data_info = response.json()

        target = next(
            item
            for item in bulk_data_info["data"]
            if item["type"] == "default_cards"
        )
        download_url = target["download_uri"]

        # Download the JSON list
        file_response = requests.get(
            download_url, headers=HEADERS, timeout=TIMEOUT
        )
        file_response.raise_for_status()

        cards: list[dict[str, Any]] = file_response.json()
    except Exception:
        logger.exception("Error fetching bulk data")
        return []
    return cards


def download_card(
    card: dict[str, Any], output_dir: Path
) -> tuple[bool, str | None]:
    """Save one card image; return (success, message)."""
    try:
        # Identify the card and prepare file path
        set_code = card.get("set", "unknown").upper()
        collector_num = str(card.get("collector_number", "0"))
        # Sanitize collector number
        safe_num = re.sub(r"[^\w\-_\. ]", "_", collector_num)

        file_name = f"magic-{set_code}-{safe_num}.webp"
        file_path = output_dir / file_name

        # Check if the card already exists
        if file_path.exists():
            return True, "Skipped (Already exists)"

        # Extract Image URL
        image_url = None
        if "image_uris" in card:
            image_url = card["image_uris"].get("normal")
        elif "card_faces" in card:
            # For double-faced cards, take the front face
            image_url = (
                card["card_faces"][0].get("image_uris", {}).get("normal")
            )

        if not image_url:
            return False, "Missing image field"

        # Download the image
        img_response = requests.get(
            image_url, headers=HEADERS, timeout=TIMEOUT
        )
        img_response.raise_for_status()

        # Process and Save
        img: Image.Image = Image.open(BytesIO(img_response.content))

        # Convert to RGB if necessary
        # Removes transparency/alpha channel
        if img.mode in ("RGBA", "P"):
            img = img.convert("RGB")

        # Normalize size to your specific dimensions
        img = img.resize((245, 337), Image.Resampling.LANCZOS)
        img.save(file_path, "WEBP", quality=85)
    except (requests.RequestException, OSError, ValueError) as e:
        return False, str(e)
    else:
        return True, None
