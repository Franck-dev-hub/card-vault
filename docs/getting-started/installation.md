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

See [Configuration](configuration.md) for the variables you may set.

## Build and run

```bash
castor                   # list all tasks
castor docker:build      # build and start the dev stack, prints the URLs
castor setup:backend     # install PHP dependencies
```

## Tests and lint

```bash
castor ci           # every CI check
castor lint --fix   # auto-fix what can be, then lint
```

## Managing dependencies

```bash
castor backend:composer -- --version   # castor's own flags (-v, -q, -n, -h, --version) go after --
```

## Migrations

```bash
castor backend:migrate        # apply pending migrations
castor backend:migrate-diff   # generate a migration from entity changes
castor reset                  # wipe the dev database and replay every migration
```
