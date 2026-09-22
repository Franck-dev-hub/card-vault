# Configuration

All configuration goes through environment files, layered per environment.
`.env` is the committed base placeholder; run `castor setup:env` to generate the
gitignored local overrides that hold the real secrets:

```bash
castor setup:env    # .env.local (dev, full copy with generated secrets)
```

Each compose invocation reads the chain `.env` → per-env override →
local overrides, with later files winning:

- dev: `.env` + `.env.local`

Real secrets are never committed. The preprod and prod environments and their
secret overrides are managed by the maintainer.

## Variables

| Variable                                              | Description                                                  |
|-------------------------------------------------------|--------------------------------------------------------------|
| `PROJECT_NAME`                                        | Compose project name                                         |
| `PROJECT_ENV`                                         | Environment, `dev` for local work                            |
| `PROJECT_DOMAIN`                                      | Local domain (e.g. `card-vault.localhost`)                   |
| `PROJECT_USER_ID` / `PROJECT_GROUP_ID`                | Host user/group for volume permissions                       |
| `PHP_VERSION`                                         | PHP image tag                                                |
| `FRANKENPHP_VERSION`                                  | FrankenPHP image tag                                         |
| `COMPOSER_VERSION`                                    | Composer image tag                                           |
| `NODEJS_VERSION`                                      | Node image tag                                               |
| `PYTHON_VERSION`                                      | Python image tag                                             |
| `POSTGRES_VERSION`                                    | PostgreSQL image tag                                         |
| `REDIS_VERSION`                                       | Redis image tag                                              |
| `CADDY_VERSION`                                       | Caddy image tag                                              |
| `PHP_EXTENSIONS`                                      | PHP extensions installed at build time                       |
| `SESSION_LIFETIME`                                    | Server-side session lifetime, also the Redis key TTL         |
| `REDIS_CACHE_MAXMEMORY`                               | Memory cap of the cache instance, `allkeys-lru` eviction     |
| `REDIS_SESSION_MAXMEMORY`                             | Memory cap of the session instance, `noeviction`             |
| `APP_SECRET`                                          | Symfony app secret (random value, required)                  |
| `XDEBUG_MODE`                                         | Dev only, e.g. `debug`                                       |
| `POSTGRES_DB` / `POSTGRES_USER` / `POSTGRES_PASSWORD` | Database credentials                                         |
| `PGADMIN_PASSWORD`                                    | pgAdmin password (dev)                                       |
| `HF_TOKEN`                                            | Hugging Face token, required to download models on first run |

The API also reads two DSNs, injected by the compose files and defaulted in
`apps/api/.env` so that PHPUnit resolves them without Docker:

| Variable            | Description                                |
|---------------------|--------------------------------------------|
| `REDIS_CACHE_URL`   | Cache instance, backs the `cache.api` pool |
| `REDIS_SESSION_URL` | Session instance, backs the session handler |

## Secrets

- Never commit a local override (`.env.local`) or any token.
- Rotate any value that leaks (tokens, passwords).
