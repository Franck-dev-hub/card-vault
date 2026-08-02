import requests
from PIL import Image
from io import BytesIO

# Pokémon Configuration
API_URL = "https://api.tcgdex.net/v2/fr/cards"
TIMEOUT = 60
HEADERS = {"User-Agent": "CardVaultScrap/1.0"}


def fetch_all_cards():
    # Fetch the list of all cards
    print("Requesting data from TCGDex")
    try:
        response = requests.get(API_URL, headers=HEADERS, timeout=TIMEOUT)
        response.raise_for_status()
        return response.json()
    except requests.RequestException as e:
        print(f"Error fetching list: {e}")
        return []


def download_card(card, output_dir):
    # Download and save a single card
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
        return True, None

    except KeyError:
        return False, "Missing image field"
    except requests.RequestException as e:
        return False, f"Download error: {str(e)}"
    except Exception as e:
        return False, f"Processing error: {str(e)}"
