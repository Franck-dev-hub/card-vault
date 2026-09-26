---
status: accepted
---

# Self-host the analytics, with a second database engine

Matomo runs on our own server, with its own MariaDB, in a Docker setup separate
from the app: Matomo supports only MySQL and MariaDB, not PostgreSQL.\
It costs nothing, and visitor data stays on our server, with IPs anonymised, so
no analytics provider is added to the processing register.\
Redeploying the app never restarts the analytics.

## Considered options

- Matomo Cloud: nothing to run, but about 20 € a month and one more
  sub-processor.

## Consequences

- MariaDB needs its own backups (#28) and updates, next to PostgreSQL.
