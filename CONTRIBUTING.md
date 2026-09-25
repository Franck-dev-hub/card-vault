# Contributing

Thanks for helping out.\
Card Vault is a monorepo: Caddy proxy, Symfony API, Angular frontend and FastAPI ML service, all run with Docker Compose.

## Getting started

1. Run the dev stack: [installation](docs/getting-started/installation.md).
2. Read the [architecture overview](docs/architecture/overview.md) before touching code.

The [roadmap](docs/contributing/roadmap.md) shows what is planned.\
The [glossary](CONTEXT.md) sets the words to use, and [docs/adr](docs/adr/) explains the decisions that look surprising.

## Branches and commits

Branch from `develop`, open your pull request against `develop`, and name the branch after its issue: `fix/61-my-own-fix`.

Commit messages look like this:

```
[Fix] #61 Close the public /ml route on every proxy
```

- Types: `Feature`, `Chore`, `Fix`, `Hotfix`, `Refactor`, `Doc`, `Test`, `Style`, `Release`.
- Start with a verb, stay under 70 characters, and say what changed rather than how.
- Skip the issue ID only for untracked work, like chores or spikes.

## Language

- Issues are in French or in English
- Code, docs and commits are in English
- No em dashes

## Code

- Comments explain why, and only when the code can't.\
  Most code needs none.
- No `var_dump()`, `dd()` or `dump()`.
- PHP: Yoda conditions (`null === $x`), PHPStan at max level, PHP CS Fixer.
- TypeScript: strict mode, ESLint, Vitest, Playwright.
- Python: ruff, mypy, pytest.
- Backend: no business logic in controllers, an interface for every external integration, repositories, API Platform DTOs.
- Frontend: standalone components; features in `src/app/features/`, shared UI in `src/app/shared/`, cross-cutting code in `src/app/core/`; API calls only through services.
- ML: `def` handlers for CPU-bound inference; model and FAISS index loaded once at startup.
- Tests: normalisers against a payload captured upstream; HTTP clients with `MockHttpClient` (nominal, upstream 404, upstream 5xx).

## Before opening a pull request

- `castor ci` passes.\
  GitHub Actions runs the same checks.
- If you added or changed an endpoint, module or component, update its architecture doc in the same pull request (see the [docs index](docs/README.md)).

## Issues and security

Bugs and feature requests go in [GitHub issues](https://github.com/Franck-dev-hub/card-vault/issues).\
For a security issue, don't open a public issue: follow the [security policy](SECURITY.md).
