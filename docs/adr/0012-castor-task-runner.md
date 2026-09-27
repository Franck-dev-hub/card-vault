---
status: accepted
---

# Run every task through Castor, in dev and in CI

Castor, a PHP task runner, drives the whole monorepo: the API, the frontend and
the ML service.\
CI calls the same Castor tasks as developers, so each command has one
definition and `castor ci` runs what GitHub Actions runs.\
On a developer machine Castor runs commands inside the Docker stack; in CI it
runs them directly in the job's image, and it refuses to start Docker there.

## Considered options

- Make: already known, but its syntax is harder to write and read than PHP
  functions, and it offers nothing Castor lacks.

## Consequences

- Contributors install the static Castor binary, version 1.7 or later.
- A new check goes in a Castor task first, then in the CI workflow as a call to
  that task.
