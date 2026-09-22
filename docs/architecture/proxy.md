# Proxy architecture

Caddy is the single entry point and terminates TLS. The FrankenPHP container
embeds its own Caddy on port 8000.

## Routing (`docker/caddy/proxy.Caddyfile`)

| Prefix       | Target             | Port            |
|--------------|--------------------|-----------------|
| `/api/*`     | api (FrankenPHP)   | 8000            |
| `/bundles/*` | api (FrankenPHP)   | 8000            |
| `/ml/*`      | ml (FastAPI)       | 5000            |
| `/`          | frontend (Angular) | 80, 4200 in dev |

Security headers on every response: `X-Content-Type-Options`, `X-Frame-Options`,
`Referrer-Policy`, `Strict-Transport-Security`.

Everything is served from one origin, so CORS never applies and nothing sets
`Access-Control-*` headers. Moving the frontend to its own origin would mean
adding that layer, with an exact origin and `Allow-Credentials` for the session
cookie.

## Dev overlay

- `docker/compose.yaml`: base stack (caddy, api, frontend, ml, database,
  redis-cache, redis-session) with healthchecks and `restart: unless-stopped`.
- `docker/compose.dev.yaml`: dev overlay (hot reload, mailpit, pgadmin,
  redisinsight).
- `castor up` starts the dev environment.

TLS and the preprod/prod overlays are managed by the maintainer.
