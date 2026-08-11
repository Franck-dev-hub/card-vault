# Installation

## Prerequisites

- Docker with Docker Compose
- GNU Make
- Git

## Clone

```bash
git clone https://github.com/Franck-dev-hub/card-vault.git
cd card-vault
```

## Environment

Generate the gitignored local environment overrides (secrets, database
credentials, `HF_TOKEN`, ...). Fill in `HF_TOKEN` by hand afterwards.

```bash
make env
```

See [Configuration](configuration.md) for the full variable list.

## Build and run

```bash
make            # print all targets
make dev/build  # build and start the dev stack (first launch)
make dev/up     # start the stack after a build
```

## URLs

| Service      | URL                                  |
|--------------|--------------------------------------|
| Frontend     | http://card-vault.localhost          |
| Backend API  | http://card-vault.localhost/api      |
| API docs     | http://card-vault.localhost/api/docs |
| ML service   | http://card-vault.localhost/ml       |
| pgAdmin      | http://localhost:5050                |
| RedisInsight | http://localhost:5540                |
| Mailpit      | http://localhost:8025                |

## Tests and lint

```bash
make lint           # lint all stacks
make test/backend   # PHPUnit
make test/frontend  # Vitest (needs apps/frontend deps)
make test/ml        # pytest
make test/e2e       # Playwright
make ci             # lint + security + all tests
```

## Migrations

```bash
make migrate        # apply pending migrations
make migrate-diff   # generate a migration from entity changes
```

## Dev environment

```bash
make dev/up         # dev stack (hot reload, mailpit, pgadmin, redisinsight)
```

Preprod and prod environments are managed by the maintainer and are not
covered here.

## Next steps

- Work through [troubleshooting](troubleshooting.md) if something fails.
- Read the [architecture overview](../architecture/overview.md) before touching
  code.
- Read the [contributing guidelines](../contributing/guidelines.md) before your
  first commit.
