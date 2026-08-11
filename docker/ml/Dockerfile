ARG PYTHON_VERSION=3.12

# ---------------------------------------------------------------------------
# ML service: FastAPI + PyTorch + FAISS + Hugging Face
# ---------------------------------------------------------------------------
FROM python:${PYTHON_VERSION}-slim AS ml_base

ARG USER
ARG USER_ID
ARG GROUP_ID

ENV PYTHONDONTWRITEBYTECODE=1
ENV PYTHONUNBUFFERED=1
ENV UV_PROJECT_ENVIRONMENT=/opt/venv
ENV UV_NO_SYNC=1
ENV HF_HOME=/tmp/hf

COPY --from=ghcr.io/astral-sh/uv:latest /uv /uvx /bin/

RUN apt-get update \
    && apt-get install -y --no-install-recommends curl gcc g++ \
    && rm -rf /var/lib/apt/lists/*

RUN addgroup --gid ${GROUP_ID} ${USER} \
    && adduser --system --uid ${USER_ID} --home /home/${USER} --shell /bin/bash --ingroup ${USER} ${USER}

WORKDIR /app

COPY apps/ml/pyproject.toml apps/ml/uv.lock ./
RUN uv sync --frozen --no-dev

COPY apps/ml ./
RUN chown -R ${USER_ID}:${GROUP_ID} /app
RUN mkdir -p /tmp/hf && chown ${USER_ID}:${GROUP_ID} /tmp/hf

ENV PATH="/opt/venv/bin:$PATH"

USER ${USER}

EXPOSE 5000

HEALTHCHECK --interval=30s --timeout=5s --start-period=180s --retries=3 \
  CMD curl -f http://127.0.0.1:5000/ml/health || exit 1

CMD ["uvicorn", "main:app", "--host", "0.0.0.0", "--port", "5000"]
