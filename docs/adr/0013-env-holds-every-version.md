---
status: accepted
---

# `.env` holds every runtime and tool version

`.env` is the only place a runtime or tool version is written: Dockerfiles,
Compose, CI and deploy workflows read it, and Renovate scans it in Scan Only
mode, opening no pull request.

## Considered options

- Defaults in Dockerfile ARGs and `packageManager`, kept equal by a check:
  two sources, and pnpm silently follows `packageManager`.
- GitHub repository variables: invisible to Renovate and to `castor ci`.

## Consequences

- Version ARGs in Dockerfiles have no default: a build outside Compose or CI
  must pass them.
- pnpm has no `packageManager`: install it at `PNPM_VERSION`.
- A digest pin lives in `.env` too, next to its version.
