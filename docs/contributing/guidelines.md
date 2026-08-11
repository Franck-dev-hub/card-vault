# Contributing guidelines

## Language

- Issues and tickets: French or English
- Code, docs and commits: British English (colour, behaviour, initialise,
  centre, etc.).
- Never use em dashes. Use commas, semicolons, colons, or full stops instead.

## Commits

```
[Type] Short, clear message
```

- Types: `Feature`, `Chore`, `Fix`, `Hotfix`, `Refactor`, `Doc`, `Test`,
  `Style`, `Release`, `WIP`.
- When work is tracked externally, the ticket ID goes in the branch name and
  every commit message: `[Type] #ID Description`.
- Start with a verb, keep under 70 characters, describe what changed not how.
- Commits without a ticket ID are acceptable only for untracked work (chores,
  spikes).

## Code

- Comment only what the code can't state itself. Default to none. When needed,
  a short clause explaining why, never a narration.
- No `var_dump()`, `dd()`, `dump()`.
- PHP: Yoda conditions (`null === $x`), PHPStan at max level, PHP CS Fixer.
- TypeScript: ESLint + strict TypeScript, Vitest, Playwright.
- Python: ruff + mypy, pytest.
- Backend: no business logic in controllers, interfaces for every external
  integration, repository pattern, API Platform DTOs.
- Frontend: feature modules in `src/app/features/`, shared UI in
  `src/app/shared/`, cross-cutting in `src/app/core/`, API calls only through
  services.
- ML: `def` handlers for CPU-bound inference, model and FAISS index loaded once
  at startup.

## Documentation

- All documentation lives under `docs/`.
- Adding or changing an endpoint, module, or component requires updating the
  matching architecture doc in the same PR. One table row per addition.
- See [documentation index](../README.md).

## Quality bar

- Run `make ci` before finishing any work: lint + security + backend/frontend/ml
  tests. Same checks run on every PR in GitHub Actions.
- E2E (Playwright) must cover critical paths before release.

## Reporting issues

- Bug reports and feature requests via GitHub issues.
- Security issues: follow [SECURITY.md](../../SECURITY.md), never open a public
  issue.
