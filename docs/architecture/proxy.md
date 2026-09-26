# Proxy architecture

Caddy is the single entry point and terminates TLS.\
The FrankenPHP container embeds its own Caddy on port 8000.

## Routing (`docker/caddy/proxy.Caddyfile`)

| Prefix       | Target             | Port            |
|--------------|--------------------|-----------------|
| `/api/*`     | api (FrankenPHP)   | 8000            |
| `/bundles/*` | api (FrankenPHP)   | 8000            |
| `/ml/*`      | none, answers 404  |                 |
| `/`          | frontend (Angular) | 80, 4200 in dev |

The ML service is internal: only the backend reaches it, at `http://ml:5000` on
the Docker network.\
The explicit 404 stops `/ml/*` from falling through to the Angular SPA, which
would answer 200.

Security headers on every response: `X-Content-Type-Options`, `X-Frame-Options`,
`Referrer-Policy`, `Strict-Transport-Security`.\
The dev proxy (`proxy.dev.Caddyfile`, HTTP only) sets the first two.

Everything is served from one origin, so nothing sets `Access-Control-*`
headers: see [ADR 0003](../adr/0003-internal-ml-single-origin.md).

## Dev overlay

- `docker/compose.yaml`: base stack (caddy, api, frontend, ml, database,
  redis-cache, redis-session) with healthchecks and `restart: unless-stopped`.
- `docker/compose.dev.yaml`: dev overlay (hot reload, mailpit, pgadmin,
  redisinsight).
- `castor up` starts the dev environment.

TLS and the preprod/prod overlays are managed by the maintainer.
