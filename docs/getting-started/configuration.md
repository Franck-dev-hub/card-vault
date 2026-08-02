# Configuration

All configuration goes through environment files. Copy the template with
`make env` (creates `.env`, `.env.prod`, `.env.preprod`) or manually:

```bash
cp .env.example .env
```

`.env` drives the dev stack, `.env.preprod` and `.env.prod` drive their
respective environments. Secrets are never committed.

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
| `PLAYWRIGHT_VERSION`                                  | Playwright version used by e2e tests                         |

## Secrets

- Never commit `.env`, `.env.prod`, `.env.preprod`, or any token.
- Rotate any value that leaks (tokens, passwords).
- Production secrets live only on the deploy host and in the CI secrets store.
