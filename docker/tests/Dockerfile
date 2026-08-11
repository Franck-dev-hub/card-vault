ARG NODEJS_VERSION=22
ARG PLAYWRIGHT_VERSION=1.49.0

# ---------------------------------------------------------------------------
# Playwright E2E runner (CI): Angular app tests
# ---------------------------------------------------------------------------
FROM mcr.microsoft.com/playwright:v${PLAYWRIGHT_VERSION}-noble AS playwright

ARG NODEJS_VERSION
ARG USER
ARG USER_ID
ARG GROUP_ID

RUN addgroup --gid ${GROUP_ID} ${USER} \
    && adduser --system --uid $USER_ID --home /home/$USER --shell /bin/bash $USER \
    && usermod -g $USER $USER

USER ${USER}

WORKDIR /app

CMD ["npx", "playwright", "test"]
