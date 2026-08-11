# Configuration

All configuration goes through environment files, layered per environment.
`.env` is the committed base placeholder; `.env.prod` and `.env.preprod` are
committed overrides that set the environment-specific values on top of it.
Run `make env` to generate the gitignored local overrides that hold the real
secrets:

```bash
make env            # .env.local (dev, full copy with generated secrets)
make env/prod       # .env.prod.local (secret overrides only)
make env/preprod    # .env.preprod.local (secret overrides only)
```

Each compose invocation reads the chain `.env` → per-env override →
local overrides, with later files winning:

- dev: `.env` + `.env.local`
- prod: `.env` + `.env.prod` + `.env.prod.local`
- preprod: `.env` + `.env.preprod` + `.env.preprod.local`

Real secrets are never committed.

## Variables

| Variable                                              | Description                                                  |
|-------------------------------------------------------|--------------------------------------------------------------|
| `PROJECT_NAME`                                        | Compose project name                                         |
| `PROJECT_ENV`                                         | Environment: `dev`, `preprod`, `prod`                        |
| `PROJECT_DOMAIN`                                      | Public domain (e.g. `card-vault.fr`)                         |
| `PROJECT_USER_ID` / `PROJECT_GROUP_ID`                | Host user/group for volume permissions                       |
| `PROJECT_IMAGE_TAG`                                   | Image tag pulled for preprod/prod                            |
| `DOCKER_REGISTRY`                                     | Registry (e.g. `ghcr.io/franck-dev-hub`)                     |
| `PHP_VERSION`                                         | PHP image tag                                                |
| `FRANKENPHP_VERSION`                                  | FrankenPHP image tag                                         |
| `COMPOSER_VERSION`                                    | Composer image tag                                           |
| `NODEJS_VERSION`                                      | Node image tag                                               |
| `PYTHON_VERSION`                                      | Python image tag                                             |
| `POSTGRES_VERSION`                                    | PostgreSQL image tag                                         |
| `REDIS_VERSION`                                       | Redis image tag                                              |
| `CADDY_VERSION`                                       | Caddy image tag                                              |
| `PHP_EXTENSIONS`                                      | PHP extensions installed at build time                       |
| `SESSION_LIFETIME`                                    | Session cookie lifetime in seconds                           |
| `APP_SECRET`                                          | Symfony app secret (random value, required)                  |
| `XDEBUG_MODE`                                         | Dev only, e.g. `debug`                                       |
| `POSTGRES_DB` / `POSTGRES_USER` / `POSTGRES_PASSWORD` | Database credentials                                         |
| `PGADMIN_PASSWORD`                                    | pgAdmin password (dev)                                       |
| `HF_TOKEN`                                            | Hugging Face token, required to download models on first run |

## Secrets

- Never commit a local override (`.env.local`, `.env.prod.local`,
  `.env.preprod.local`) or any token.
- Rotate any value that leaks (tokens, passwords).
- Production secrets live only on the deploy host (`.env.prod.local`) and in
  the CI secrets store.
