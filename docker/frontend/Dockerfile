ARG NODEJS_VERSION=22

# ---------------------------------------------------------------------------
# Build stage: compile the Angular app
# ---------------------------------------------------------------------------
FROM node:${NODEJS_VERSION}-alpine AS build

RUN corepack enable

WORKDIR /app

COPY apps/frontend/package.json apps/frontend/pnpm-lock.yaml* ./
RUN pnpm install --frozen-lockfile || pnpm install

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
