# Card Vault: development conventions

## Stack

| Part     | Technology |
|----------|------------|
| Backend  | PHP 8.4, Symfony 7, API Platform 4, Doctrine ORM, PostgreSQL 16, Redis 7 |
| Frontend | Angular (TS), pnpm, nginx (prod static), Jest, Playwright |
| ML       | Python 3.12, FastAPI, PyTorch, FAISS, Hugging Face (DINOv2) |
| Proxy    | Caddy (auto-HTTPS, replaces nginx + certbot) |
| Infra    | Docker Compose (dev/preprod/prod/ci overlays), GitHub Actions, GHCR, Tailscale (deploy on Mac M1 Mini) |

## Language

- **Issues and tickets**: French. Most contributors are French speakers and not
  all are fluent in English.
- **Code, docs and commits**: British English (colour, behaviour, initialise,
  centre, etc.).
- Never use em dashes. Use commas, semicolons, colons, or full stops instead.

## Git commits

### Format

```
[Type] Short, clear message
```

### Types

`Feature`, `Chore`, `Fix`, `Hotfix`, `Refactor`, `Doc`, `Test`, `Style`, `Release`, `WIP`

### Ticket ID

When work is tracked externally, the ticket ID must appear in both the branch
name and every commit message on that branch.

```
[Type] #ID Description
```

The ticket ID ties every commit to its originating task. Commits without a
ticket ID are only acceptable for untracked work (internal chores, exploratory
spikes).

### Message rules

- Start with a verb.
- Keep it short, max 70 characters.
- Describe *what* changed, not *how*.

## Code comments

Applies to all languages (PHP, TypeScript, Python, shell, YAML).

Comment only what the code can't state itself. Default to none.

**AI agents: be concise. Default to no comment. When one is truly needed, write
a short clause, never a docblock paragraph, narration, or multi-line
explanation.**

- Explain the **why** (constraint, trade-off, gotcha), never the **what**. Do
  not restate the line.
- Dev/tech-oriented, as short as possible: a clause, not a sentence. No ticket
  refs, no changelog, no narration.
- Match the surrounding file's comment density; prefer a clearer name/type over
  a comment; drop stale ones when editing.

## Code style

- **Yoda conditions**: in equality/identity checks, put the constant on the
  left (`null === $x`, `'' === $name`). A mistyped `=` then fails to compile
  instead of silently assigning. Enforced by PHP-CS-Fixer. Variable-vs-variable
  checks, `instanceof`, and ordering operators (`<`, `>`) are left untouched.
- No `var_dump()`, `dd()`, `dump()` committed.

## Conventions

### Cross-cutting

- [Contributing guidelines](docs/contributing/guidelines.md)
- [Architecture overview](docs/architecture/overview.md)
- [Getting started](docs/getting-started/installation.md)
- [Configuration](docs/getting-started/configuration.md)

Adding or changing an endpoint, module, or component requires updating the
matching architecture doc (`docs/architecture/*.md`) in the same PR. One table
row per addition.

### Backend (`apps/api/`)

- No business logic in controllers: always use dedicated services.
- Interfaces for every external integration point (TCG APIs, ML service).
- Repository pattern: no direct ORM calls from controllers.
- API Platform DTOs rather than exposing entities directly when needed.
- Entity mapping tests for each entity, unit tests with mocks for services.
- PHPStan at max level, PHP CS Fixer.
- See [backend architecture](docs/architecture/backend.md).

### Frontend (`apps/frontend/`)

- Feature modules under `src/app/features/`, shared UI in `src/app/shared/`,
  cross-cutting in `src/app/core/`.
- Lazy-loaded routes per feature module.
- API calls only through services (injectable), never directly in components.
- See [frontend architecture](docs/architecture/frontend.md).

### ML (`apps/ml/`)

- `def` handlers (FastAPI threadpool), never blocking async handlers for
  CPU-bound inference.
- Load the FAISS index and model once at startup, keep in memory.
- ruff + mypy, pytest.
- See [ML architecture](docs/architecture/ml.md).

### Docker / Infra

- Multi-stage images, no build dependencies in prod images.
- Healthcheck on every service.
- Named volumes for persistence, secrets via environment variables only.
- `restart: unless-stopped` on all services.
- Never run `docker compose down -v` in build/deploy targets (destroys data).

### Quality bar

- Run `make ci` before finishing any work: lint + security + backend/frontend/ml
  tests.
- E2E (Playwright) must cover critical paths before release.

## Project commands

Run from the repository root. `make` alone prints help.

```bash
make env                      # create .env, .env.prod, .env.preprod from templates
make dev/up                   # start dev stack
make dev/build                # build + start dev stack
make dev/build/{service}      # rebuild one service (api, frontend, ml, ...)
make preprod/up prod/up       # start preprod/prod (pulls GHCR images)
make lint                     # lint all stacks (backend, frontend, ml)
make sec                      # security audits (composer, pnpm, pip-audit)
make test/backend             # PHPUnit
make test/frontend            # Jest (needs apps/frontend deps)
make test/ml                  # pytest
make test/e2e                 # Playwright
make ci                       # lint + sec + backend/frontend/ml tests (same as CI)
make migrate                  # doctrine:migrations:migrate
make migrate-diff             # generate a migration
make prune                    # delete merged local branches
```

## Working rules for an LLM

- Read the relevant spec or architecture doc before touching code in that layer.
- Follow the existing code patterns and naming in `apps/`; consistency beats
  personal preference.
- Never introduce comments unless asked; code should be self-documenting.
- Never commit secrets or credentials. Use env vars for any configuration.
- Run the relevant `make lint/*`, `make test/*` (and `make ci` before finishing)
  and confirm the output before claiming completion.
- Do not run destructive commands (`down -v`, force push, hard reset) without
  explicit confirmation.
