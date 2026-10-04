# check=skip=InvalidDefaultArgInFrom
ARG NODEJS_VERSION
ARG PNPM_VERSION

# ---------------------------------------------------------------------------
# Build stage: compile the Angular app
# ---------------------------------------------------------------------------
FROM node:${NODEJS_VERSION}-alpine AS build

ARG PNPM_VERSION
RUN npm install -g pnpm@${PNPM_VERSION}

WORKDIR /app

COPY apps/frontend/package.json apps/frontend/pnpm-lock.yaml apps/frontend/pnpm-workspace.yaml ./
RUN pnpm install --frozen-lockfile

COPY apps/frontend .
RUN pnpm build

# ---------------------------------------------------------------------------
# Runtime stage: static assets served by nginx
# ---------------------------------------------------------------------------
FROM nginx:alpine AS runtime

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=build /app/dist/frontend/browser /usr/share/nginx/html

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD wget -q -O /dev/null http://127.0.0.1/ || exit 1
