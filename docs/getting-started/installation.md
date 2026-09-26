# Installation

## Prerequisites

- Docker with Docker Compose
- Castor >=1.7
- Git

## Clone

```bash
git clone https://github.com/Franck-dev-hub/card-vault.git
cd card-vault
```

## Environment

Generate `.env.local` with random dev secrets, then fill in `HF_TOKEN` by hand.\
The same task enables the pre-push hook, which runs `castor ci` before every
push.

```bash
castor setup:env
```

See [Configuration](configuration.md) for the full variable list.

## Build and run

```bash
castor                   # list all tasks
castor docker:build      # build and start the dev stack (first launch)
castor setup:backend     # install PHP dependencies
castor setup:frontend    # install JS dependencies
castor up                # start the stack after a build
castor stop              # stop the stack
castor terminal          # shell in the api container (`sf` = bin/console)
```

App tasks run inside the dev containers, so the stack must be up.

## URLs

| Service      | URL                                  |
|--------------|--------------------------------------|
| Frontend     | http://card-vault.localhost          |
| Backend API  | http://card-vault.localhost/api      |
| API docs     | http://card-vault.localhost/api/docs |
| pgAdmin      | http://localhost:5050                |
| RedisInsight | http://localhost:5540                |
| Mailpit      | http://localhost:8025                |

## Tests and lint

```bash
castor lint                  # lint all stacks
castor lint --fix            # auto-fix what can be, then lint
castor lint:backend          # one stack: backend, frontend, ml
castor lint:backend:phpstan  # one tool, named after its stack
castor security:all          # dependency audits
castor tests:backend         # PHPUnit
castor tests:frontend        # Vitest
castor tests:ml              # pytest
castor tests:e2e             # Playwright
castor tests:backend:infection  # Infection mutation testing
castor ci:backend            # one stack's CI checks
castor ci                    # every stack; Infection runs on the whole tree
```

## Managing dependencies

```bash
castor frontend:pnpm add <package>
castor frontend:pnpm update --latest
castor backend:composer update
castor ml:uv add <package>
castor backend:composer -- --version   # castor's own flags (-v, -q, -n, -h, --version) go after --
```

## Migrations

```bash
castor backend:migrate        # apply pending migrations
castor backend:migrate-diff   # generate a migration from entity changes
castor reset                  # wipe the dev database and replay every migration
```

Preprod and prod environments are managed by the maintainer and are not covered
here.

## Next steps

- Work through [troubleshooting](troubleshooting.md) if something fails.
- Read the [architecture overview](../architecture/overview.md) before touching
  code.
- Read the [contributing guidelines](../../CONTRIBUTING.md) before your first
  commit.
