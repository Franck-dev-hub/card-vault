# Contributing

Thanks for helping out.

## Getting started

1. Run the dev stack: [installation](docs/getting-started/installation.md).
2. Read the [architecture overview](docs/architecture/overview.md) before
   touching code.

The [roadmap](docs/contributing/roadmap.md) shows what is planned.\
The [glossary](CONTEXT.md) sets the words to use, and [docs/adr](docs/adr/)
explains the decisions that look surprising.

## Branches and commits

Branch from `develop`, open your pull request against `develop`, and name the
branch after its issue: `fix/61-my-own-fix`.

Commit messages look like this:

```
[Fix] #61 Close the public /ml route on every proxy
```

- Types: `Feature`, `Chore`, `Fix`, `Hotfix`, `Refactor`, `Doc`, `Test`,
  `Style`, `Release`.
- Start with a verb, stay under 70 characters, and say what changed rather than
  how.
- Skip the issue ID only for untracked work, like quick maintenance or spikes.

## Language

- Issues are in French or in English
- Code, docs and commits are in English
- No em dashes

## Code

- Comments explain why, and only when the code can't.\
  Most code needs none.
- No `var_dump()`, `dd()` or `dump()`.
- Each app has its own layout rules: [backend](docs/architecture/backend.md),
  [frontend](docs/architecture/frontend.md), [ML](docs/architecture/ml.md).

## Before opening a pull request

- `castor ci` passes.\
  GitHub Actions runs the same checks.
- If you added or changed an endpoint, module or component, update its
  architecture doc in the same pull request (see the
  [docs index](docs/README.md)).

## Issues and security

Bugs and feature requests go in
[GitHub issues](https://github.com/Franck-dev-hub/card-vault/issues).\
For a security issue, don't open a public issue: follow the
[security policy](SECURITY.md).
