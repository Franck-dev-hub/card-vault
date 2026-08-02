ARG PYTHON_VERSION=3.12

# ---------------------------------------------------------------------------
# ML service (copied from V1): FastAPI + PyTorch + FAISS + Hugging Face
# ---------------------------------------------------------------------------
FROM python:${PYTHON_VERSION}-slim AS ml_base

ARG USER
ARG USER_ID
ARG GROUP_ID

ENV PYTHONDONTWRITEBYTECODE=1
ENV PYTHONUNBUFFERED=1

RUN apt-get update \
    && apt-get install -y --no-install-recommends curl \
    && rm -rf /var/lib/apt/lists/*

RUN addgroup --gid ${GROUP_ID} ${USER} \
    && adduser --system --uid ${USER_ID} --home /home/${USER} --shell /bin/bash --ingroup ${USER} ${USER}

WORKDIR /app

COPY apps/ml/requirements.txt ./
RUN pip install --no-cache-dir -r requirements.txt

COPY apps/ml ./
RUN chown -R ${USER_ID}:${GROUP_ID} /app

USER ${USER}

EXPOSE 5000

HEALTHCHECK --interval=30s --timeout=5s --start-period=15s --retries=3 \
  CMD curl -f http://127.0.0.1:5000/ml/health || exit 1

CMD ["uvicorn", "app.main:app", "--host", "0.0.0.0", "--port", "5000"]
