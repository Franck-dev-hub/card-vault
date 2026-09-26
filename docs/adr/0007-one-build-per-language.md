---
status: accepted
---

# Translate the frontend at build time, one build per language

The frontend is translated when it is built, with `@angular/localize`: each
language is a complete site of its own, served under its own path (`/fr/`,
`/en/`).\
It is part of Angular and adds nothing for the visitor to download, and search
engines can only index each language if it has its own URL.\
Switching language opens the other site, so the page reloads.

## Considered options

- A runtime library (ngx-translate, Transloco): switches without a reload, but
  ships every dictionary to the browser and serves every language from one URL.
- A hand-rolled dictionary: the same runtime cost, plus the tooling to maintain.

## Consequences

- Every visible string is marked `i18n` or `$localize` from the first screen.
- Each new language adds a build to CI and a path to the proxy (#77).
