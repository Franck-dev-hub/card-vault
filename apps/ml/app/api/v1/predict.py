from fastapi import APIRouter, HTTPException
from pydantic import BaseModel, Field
from app.models.model import search_card
import base64
import binascii

router = APIRouter(tags=["predict"])

MAX_IMAGE_FIELD_LENGTH = 2_000_000

class PredictRequest(BaseModel):
    image: str = Field(max_length=MAX_IMAGE_FIELD_LENGTH)


@router.post("/predict")
def post_prediction(body: PredictRequest):
    try:
        b64 = body.image.split(",", 1)[-1]
        image_data = base64.b64decode(b64, validate=True)
    except (binascii.Error, ValueError):
        raise HTTPException(status_code=400, detail="invalid base64 image")
    return search_card(image_data)
