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

RUN <<-EOF
    set -eux
    if getent group "${GROUP_ID}" >/dev/null; then
        groupmod -n "${USER}" "$(getent group "${GROUP_ID}" | cut -d: -f1)"
    else
        addgroup --gid "${GROUP_ID}" "${USER}"
    fi
    if getent passwd "${USER_ID}" >/dev/null; then
        usermod -l "${USER}" -d "/home/${USER}" -m -g "${USER}" "$(getent passwd "${USER_ID}" | cut -d: -f1)"
    else
        adduser --system --uid "${USER_ID}" --home "/home/${USER}" --shell /bin/bash "${USER}"
    fi
    usermod -g "${USER}" "${USER}"
EOF

RUN npm install -g corepack@latest

USER ${USER}

WORKDIR /app

CMD ["npx", "playwright", "test"]
