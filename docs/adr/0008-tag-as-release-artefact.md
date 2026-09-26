---
status: accepted
---

# Release a tag, not a branch

A version is released by putting a tag on a commit (`v0.3`): the tag builds one
Docker image per service, and that exact image is deployed to preprod, then to
prod once approved.\
Preprod and prod are places the image runs, not branches: moving to prod means
approving that same image, not merging code.\
Prod therefore runs the very image that was tested in preprod, byte for byte.

## Considered options

- One branch per environment: every version is built twice, so preprod
  validates an image that prod never runs.

## Consequences

- Nothing may point at a `prod` or `preprod` branch: badges, docs and CI
  triggers use `develop` or the tags (#64).
- A fix found in preprod ships as a patch tag `vX.Y.Z`, never as a commit on an
  environment.
