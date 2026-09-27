"""Route that recognises a card from a photo."""

import base64
import binascii

from fastapi import APIRouter, HTTPException
from pydantic import BaseModel, Field

from app.models.model import CardMatch, search_card

router = APIRouter(tags=["predict"])

MAX_IMAGE_FIELD_LENGTH = 2_000_000


class PredictRequest(BaseModel):
    """A card photo in base64, with or without a data URL prefix."""

    image: str = Field(max_length=MAX_IMAGE_FIELD_LENGTH)


@router.post("/predict")
def post_prediction(body: PredictRequest) -> list[CardMatch]:
    """Return the three closest known cards; 400 on invalid base64."""
    try:
        b64 = body.image.split(",", 1)[-1]
        image_data = base64.b64decode(b64, validate=True)
    except (binascii.Error, ValueError) as err:
        raise HTTPException(
            status_code=400, detail="invalid base64 image"
        ) from err
    return search_card(image_data)
