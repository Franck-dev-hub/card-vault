# Proxy architecture

Caddy serves as the single entry point. TLS is terminated by the front proxy;
the FrankenPHP container embeds its own Caddy internally on port 8000.

## Routing (`docker/caddy/proxy.Caddyfile`)

The front proxy routes by path prefix:

| Prefix  | Target              | Port |
|---------|---------------------|------|
| /api/*  | api (FrankenPHP)    | 8000 |
| /ml/*   | ml (FastAPI)        | 5000 |
| /       | frontend (Angular)  | 80   |

Security headers are set on every response: `X-Content-Type-Options`,
`X-Frame-Options`, `Referrer-Policy`, `Strict-Transport-Security`.

## CORS (`docker/caddy/api.Caddyfile`)

The FrankenPHP Caddyfile handles CORS for the API: OPTIONS preflight returns
204 with the allowed methods (`GET, POST, PATCH, DELETE, OPTIONS`) and headers
(`Content-Type, Authorization, X-Requested-With`). Origin comes from
`CADDY_CORS_ORIGIN`.

## TLS

Let's Encrypt is handled automatically by Caddy in production through the
`caddy_data` and `caddy_config` volumes.

## Deployment (Docker)

- `docker/compose.yaml`: base stack (caddy, api, frontend, ml, database, redis)
  with healthchecks and `restart: unless-stopped`.
- `docker/compose.dev.yaml`, `compose.preprod.yaml`, `compose.prod.yaml`,
  `compose.ci.yaml`: environment overlays.
- `make dev/up`, `make preprod/up`, `make prod/up` start an environment.
