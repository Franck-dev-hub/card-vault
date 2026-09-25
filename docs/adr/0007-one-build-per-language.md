---
status: accepted
---

# Translate the frontend at build time, one build per language

The frontend uses `@angular/localize`: each language is a separate, already
translated build, served under its own path (`/fr/`, `/en/`).\
It is part of Angular and costs nothing at runtime, and per-language URLs are
what prerendering and `hreflang` need.\
Switching language loads the other build, so it reloads the page.

## Considered options

- A runtime library (ngx-translate, Transloco): switches without a reload, but
  ships every dictionary to the browser and serves every language from one URL.
- A hand-rolled dictionary: the same runtime cost, plus the tooling to maintain.

## Consequences

- Every visible string is marked `i18n` or `$localize` from the first screen.
- Each new language adds a build to CI and a path to the proxy (#77).
