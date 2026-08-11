import requests
from PIL import Image
from io import BytesIO
import re

# Scryfall Configuration
BULK_INFO_URL = "https://api.scryfall.com/bulk-data"
TIMEOUT = 60
HEADERS = {"User-Agent": "CardVaultScrap/1.0"}


def fetch_all_cards():
    # Fetch the list of all cards
    print("Requesting data from Scryfall")
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

        return file_response.json()

    except Exception as e:
        print(f"Error fetching bulk data: {e}")
        return []


def download_card(card, output_dir):
    # Download and save a single card
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
        img = Image.open(BytesIO(img_response.content))

        # Convert to RGB if necessary
        # Removes transparency/alpha channel
        if img.mode in ("RGBA", "P"):
            img = img.convert("RGB")

        # Normalize size to your specific dimensions
        img = img.resize((245, 337), Image.Resampling.LANCZOS)
        img.save(file_path, "WEBP", quality=85)

        return True, None

    except Exception as e:
        return False, str(e)
