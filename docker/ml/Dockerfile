# check=skip=InvalidDefaultArgInFrom
ARG PYTHON_VERSION
ARG PYTHON_DIGEST
ARG USER=cardvault
ARG USER_ID=1000
ARG GROUP_ID=1000
ARG UV_VERSION
ARG UV_DIGEST

FROM ghcr.io/astral-sh/uv:${UV_VERSION}@${UV_DIGEST} AS uv

# ---------------------------------------------------------------------------
# ML service: FastAPI + PyTorch + FAISS + Hugging Face
# ---------------------------------------------------------------------------
FROM python:${PYTHON_VERSION}-slim@${PYTHON_DIGEST} AS ml_release

ARG USER
ARG USER_ID
ARG GROUP_ID

ENV PYTHONDONTWRITEBYTECODE=1
ENV PYTHONUNBUFFERED=1
ENV UV_PROJECT_ENVIRONMENT=/opt/venv
ENV UV_NO_SYNC=1
ENV HF_HOME=/tmp/hf

RUN addgroup --gid ${GROUP_ID} ${USER} \
    && adduser --system --uid ${USER_ID} --home /home/${USER} --shell /bin/bash --ingroup ${USER} ${USER}

WORKDIR /app

COPY apps/ml/pyproject.toml apps/ml/uv.lock ./
RUN --mount=from=uv,source=/uv,target=/bin/uv \
    uv sync --frozen --no-dev --no-build --no-cache --extra cpu
RUN /opt/venv/bin/python -c "import torch, torchvision"

COPY apps/ml ./
RUN chown -R ${USER_ID}:${GROUP_ID} /app
RUN mkdir -p /tmp/hf && chown ${USER_ID}:${GROUP_ID} /tmp/hf

ENV PATH="/opt/venv/bin:$PATH"

USER ${USER}

EXPOSE 5000

HEALTHCHECK --interval=30s --timeout=5s --start-period=180s --retries=3 \
    CMD ["python", "-c", "import urllib.request; urllib.request.urlopen('http://127.0.0.1:5000/ml/health', timeout=4)"]

CMD ["uvicorn", "main:app", "--host", "0.0.0.0", "--port", "5000"]

FROM ml_release AS ml_dev

ARG USER

USER root

COPY --from=uv /uv /uvx /bin/

RUN uv sync --frozen --no-build --extra cpu

USER ${USER}
