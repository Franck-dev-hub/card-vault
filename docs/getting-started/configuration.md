# Configuration

All configuration goes through environment files.\
`.env` is committed and holds placeholders.\
Docker Compose reads `.env`, then `.env.local`; the later file wins.

Real secrets are never committed.\
The preprod and prod environments and their secret overrides are managed by the
maintainer.

## Variables

| Variable                               | Description                                           |
|----------------------------------------|-------------------------------------------------------|
| `PROJECT_USER_ID` / `PROJECT_GROUP_ID` | Host user/group for volume permissions                |
| `XDEBUG_MODE`                          | Dev only, e.g. `debug`                                |
| `HF_TOKEN`                             | Hugging Face token, needed for the private card index |

Every other variable lives in `.env`, grouped by service.

The API also reads two DSNs, injected by the compose files and defaulted in
`apps/api/.env` so that PHPUnit resolves them without Docker:

| Variable            | Description                                 |
|---------------------|---------------------------------------------|
| `REDIS_CACHE_URL`   | Cache instance, backs the `cache.api` pool  |
| `REDIS_SESSION_URL` | Session instance, backs the session handler |
