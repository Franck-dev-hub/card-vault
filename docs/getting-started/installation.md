# Installation

## Prerequisites

- Docker with Docker Compose
- GNU Make
- Git

## Clone

```bash
git clone https://github.com/Franck-dev-hub/card_vault.git
cd card_vault
```

## Environment

Copy the environment template and fill in your own values (secrets, database
credentials, `HF_TOKEN`, ...).

```bash
make env
# or: cp .env.example .env
```

See [Configuration](configuration.md) for the full variable list.

## Build and run

```bash
make            # print all targets
make dev/build  # build and start the dev stack (first launch)
make dev/up     # start the stack after a build
```

## URLs

| Service      | URL                       |
|--------------|---------------------------|
| Frontend     | http://localhost          |
| Backend API  | http://localhost/api      |
| API docs     | http://localhost/api/docs |
| ML service   | http://localhost/ml       |
| pgAdmin      | http://localhost:5050     |
| RedisInsight | http://localhost:5540     |
| Mailpit      | http://localhost:8025     |

## Tests and lint

```bash
make lint           # lint all stacks
make test/backend   # PHPUnit
make test/frontend  # Jest (needs apps/frontend deps)
make test/ml        # pytest
make test/e2e       # Playwright
make ci             # lint + security + all tests
```

## Migrations

```bash
make migrate        # apply pending migrations
make migrate-diff   # generate a migration from entity changes
```

## Environments

```bash
make dev/up         # dev stack (hot reload, mailpit, pgadmin, redisinsight)
make preprod/up     # preprod, pulls GHCR images
make prod/up        # prod, pulls GHCR images
```
