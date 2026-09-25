---
status: accepted
---

# Release a tag, not a branch

A `v*` tag builds one image per service, and that same image, identified by its
digest, is deployed to preprod and then promoted to prod.\
Preprod and prod are deployment environments, not branches: what runs is
decided by a digest and a reviewed GitHub Environment, not by a merge.\
Prod therefore runs the exact bytes that were validated in preprod.

## Considered options

- One branch per environment: every version is built twice, so preprod
  validates an image that prod never runs.

## Consequences

- Nothing may point at a `prod` or `preprod` branch: badges, docs and CI
  triggers use `develop` or the tags (#64).
