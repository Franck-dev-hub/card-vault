# Backend architecture

API Platform exposes the API.\
The interactive OpenAPI documentation is the canonical reference:
`http://card-vault.localhost/api/docs` in dev.

## Endpoints

`Live` means the route answers today.\
Any endpoint added or implemented must flip its row here in the same PR.

| Method | Path                                         | Auth | Status  | Purpose                          |
|--------|----------------------------------------------|------|---------|----------------------------------|
| GET    | /health                                      | No   | Live    | Liveness check                   |
| GET    | /api/licence                                 | No   | Live    | List games                       |
| GET    | /api/licence/{slug}/extensions               | No   | Live    | List a game's extensions         |
| GET    | /api/licence/{slug}/extensions/{setId}/cards | No   | Live    | List an extension's cards        |
| GET    | /api/licence/{slug}/cards/{cardId}           | No   | Live    | Get a single card                |
| POST   | /api/register                                | No   | Planned | Create an account                |
| POST   | /api/login                                   | No   | Planned | Start a session                  |
| POST   | /api/logout                                  | Yes  | Planned | Destroy the session              |
| GET    | /api/me                                      | Yes  | Planned | Get the connected user           |
| GET    | /api/vault                                   | Yes  | Planned | List the vault's copies          |
| POST   | /api/vault                                   | Yes  | Planned | Add a copy                       |
| PATCH  | /api/vault/{id}                              | Yes  | Planned | Update a copy                    |
| DELETE | /api/vault/{id}                              | Yes  | Planned | Remove a copy                    |
| GET    | /api/vault/stats                             | Yes  | Planned | Vault statistics                 |
| GET    | /api/vault/recent                            | Yes  | Planned | Recently added copies            |
| GET    | /api/dashboard                               | Yes  | Planned | Dashboard data                   |
| POST   | /api/scan                                    | Yes  | Planned | Proxy an image to the ML service |

## Structure and organisation

Layout under `apps/api/src/`:

- `Controller/`: plain Symfony controllers, no business logic (`/health`).
- `Entity/`: Doctrine entities.
- `Repository/`: Doctrine repositories.
- `State/`: API Platform state providers, one per operation family.
- `Service/`: business logic and external integrations, one folder per domain
  (`Service/Licence/`), DTOs in its `Dto/` subfolder.
- `ApiResource/`: reserved for API Platform resources that are not DTOs, empty
  today.

## Security and authentication

Not implemented yet.\
The intended model is session-cookie authentication (`SESSION_LIFETIME` is
already configured in the environment), no JWT.\
Permissions planned via Symfony Voters.

## Cache and sessions

| Instance        | Holds                                           | Persistence | Memory policy |
|-----------------|-------------------------------------------------|-------------|---------------|
| `redis-cache`   | `cache.api` pool, Doctrine result cache in prod | none        | `allkeys-lru` |
| `redis-session` | PHP sessions                                    | AOF         | `noeviction`  |

Two instances, not one with two databases: `maxmemory` is a property of the
process, so a single instance cannot both evict cache entries under pressure and
never evict sessions.

Inject the pool as `CacheInterface $cacheApi`, default lifetime 7 days.\
Clear it with `cache:pool:clear cache.api`; in `prod` that also clears the
Doctrine result cache.\
Sessions live in the other instance and survive even a `FLUSHDB`.

## Database

PostgreSQL, migrations with Doctrine Migrations, no fixtures yet.

| Table  | Entity | Holds         |
|--------|--------|---------------|
| `user` | `User` | `id`, `email` |

## Custom CLI commands

None yet.\
The `castor backend:migrate` and `castor backend:migrate-diff` tasks call the
standard Doctrine commands.

## Adding a card game

Card games plug in through the external client plus normaliser pattern.\
See [Add a game](tcg-integration.md).
