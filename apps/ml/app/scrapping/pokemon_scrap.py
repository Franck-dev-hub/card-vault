"""Pokémon card images from TCGdex."""

import logging
from io import BytesIO
from typing import TYPE_CHECKING, Any

import requests
from PIL import Image

if TYPE_CHECKING:
    from pathlib import Path

# Pokémon Configuration
API_URL = "https://api.tcgdex.net/v2/fr/cards"
TIMEOUT = 60
HEADERS = {"User-Agent": "CardVaultScrap/1.0"}

logger = logging.getLogger(__name__)


def fetch_all_cards() -> list[dict[str, Any]]:
    """Return every TCGdex card, or an empty list on failure."""
    logger.info("Requesting data from TCGDex")
    try:
        response = requests.get(API_URL, headers=HEADERS, timeout=TIMEOUT)
        response.raise_for_status()
        cards: list[dict[str, Any]] = response.json()
    except requests.RequestException:
        logger.exception("Error fetching list")
        return []
    return cards


def download_card(
    card: dict[str, Any], output_dir: Path
) -> tuple[bool, str | None]:
    """Save one card image; return (success, message)."""
    try:
        # Rename card
        card_id = card.get("id", "unknown")
        file_name = f"pkmn-{card_id}.webp"
        file_path = output_dir / file_name

        # Check the card exist
        if file_path.exists():
            return True, "Skipped (Already exists)"

        # Specific card image validation
        if "image" not in card or card["image"] is None:
            return False, "Missing image field"

        # Construct URL
        image_url = card["image"] + "/low.webp"

        # Download and Save
        img_response = requests.get(
            image_url, headers=HEADERS, timeout=TIMEOUT
        )
        img_response.raise_for_status()

        # Process and save the image to disk
        img = Image.open(BytesIO(img_response.content))
        img.save(file_path)
    except KeyError:
        return False, "Missing image field"
    except requests.RequestException as e:
        return False, f"Download error: {e!s}"
    except (OSError, ValueError) as e:
        return False, f"Processing error: {e!s}"
    else:
        return True, None
