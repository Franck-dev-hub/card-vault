from fastapi import APIRouter, HTTPException
from pydantic import BaseModel
from app.models.model import search_card
import base64

router = APIRouter(tags=["predict"])


class PredictRequest(BaseModel):
    image: str


@router.post("/predict")
async def post_prediction(body: PredictRequest):
    try:
        b64 = body.image.split(",", 1)[-1]
        image_data = base64.b64decode(b64)
        results = search_card(image_data)
        return results
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))
