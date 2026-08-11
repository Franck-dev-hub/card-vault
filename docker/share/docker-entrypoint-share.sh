#!/bin/sh
set -eu

# Shared entrypoint logic sourced by app entrypoints.
# Generates the app .env from injected environment variables (12-factor style),
# then starts the requested command.

APP_DIR="${APP_DIR:-/home/${CONTAINER_USER}/sources/api}"
ENV_FILE="${APP_DIR}/.env"

if [ -f "${ENV_FILE}" ] && [ -n "${APP_DEBUG:-}" ] && [ -n "${DATABASE_URL:-}" ]; then
    # .env already present and required vars are provided: nothing to do.
    :
elif [ -n "${DATABASE_URL:-}" ] || [ -n "${REDIS_URL:-}" ]; then
    # No .env committed: build one from the injected environment.
    : > "${ENV_FILE}"
    [ -n "${APP_ENV:-}" ] && echo "APP_ENV=${APP_ENV}" >> "${ENV_FILE}"
    [ -n "${APP_SECRET:-}" ] && echo "APP_SECRET=${APP_SECRET}" >> "${ENV_FILE}"
    [ -n "${DATABASE_URL:-}" ] && echo "DATABASE_URL=${DATABASE_URL}" >> "${ENV_FILE}"
    [ -n "${REDIS_URL:-}" ] && echo "REDIS_URL=${REDIS_URL}" >> "${ENV_FILE}"
fi

# Wait for the database to be reachable before booting the app.
if [ -n "${DATABASE_URL:-}" ] && command -v php >/dev/null 2>&1; then
    echo "Waiting for the database..."
    HOST=$(printf '%s' "$DATABASE_URL" | sed -E 's|.*@([^:/]+):.*|\1|')
    PORT=$(printf '%s' "$DATABASE_URL" | sed -E 's|.*@[^:/]+:([0-9]+).*|\1|')
    PORT="${PORT:-5432}"
    for i in $(seq 1 60); do
        if php -r "\$c=@fsockopen('$HOST', $PORT); exit(\$c?0:1);" 2>/dev/null; then
            echo "Database is up."
            break
        fi
        if [ "$i" -eq 60 ]; then
            echo "Database did not become ready in time." >&2
            exit 1
        fi
        sleep 2
    done
fi
