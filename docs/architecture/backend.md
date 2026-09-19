# Backend architecture

API Platform exposes the API. The interactive OpenAPI documentation is the
canonical reference: `http://card-vault.localhost/api/docs` in dev.

## Endpoints

`Live` means the route answers today. Any endpoint added or implemented must
flip its row here in the same PR.

| Method | Path                                         | Auth | Status  | Purpose                          |
|--------|----------------------------------------------|------|---------|----------------------------------|
| GET    | /health                                      | No   | Live    | Liveness check                   |
| GET    | /api/licence                                 | No   | Live    | List licences                    |
| GET    | /api/licence/{slug}/extensions               | No   | Live    | List a game's extensions         |
| GET    | /api/licence/{slug}/extensions/{setId}/cards | No   | Live    | List an extension's cards        |
| GET    | /api/licence/{slug}/cards/{cardId}           | No   | Live    | Get a single card                |
| POST   | /api/register                                | No   | Planned | Create an account                |
| POST   | /api/login                                   | No   | Planned | Start a session                  |
| POST   | /api/logout                                  | Yes  | Planned | Destroy the session              |
| GET    | /api/me                                      | Yes  | Planned | Get the connected user           |
| GET    | /api/vault                                   | Yes  | Planned | List collection items            |
| POST   | /api/vault                                   | Yes  | Planned | Add a collection item            |
| PATCH  | /api/vault/{id}                              | Yes  | Planned | Update a collection item         |
| DELETE | /api/vault/{id}                              | Yes  | Planned | Remove a collection item         |
| GET    | /api/vault/stats                             | Yes  | Planned | Collection statistics            |
| GET    | /api/vault/recent                            | Yes  | Planned | Recently added items             |
| GET    | /api/dashboard                               | Yes  | Planned | Dashboard data                   |
| POST   | /api/scan                                    | Yes  | Planned | Proxy an image to the ML service |

## Structure and organisation

Not implemented yet. The intended layout under `apps/api/src/`:

- `Controller/`: API Platform resource controllers, no business logic.
- `Entity/`: Doctrine entities.
- `Repository/`: Doctrine repositories.
- `Service/`: business logic and external integrations (TCG APIs, ML client).
- `Dto/`: API Platform DTOs.

## Security and authentication

Not implemented yet. The intended model is session-cookie authentication
(`SESSION_LIFETIME` is already configured in the environment), no JWT.
Permissions planned via Symfony Voters.

## Database

PostgreSQL (already wired in `docker/compose.yaml`), migrations with
Doctrine Migrations, fixtures for test data. No tables defined yet.

## Custom CLI commands

None yet. The `make migrate` and `make migrate-diff` targets call the standard
Doctrine commands.

## Adding a card game

Card games plug in through the external client plus normaliser pattern. See
[Add a card game (licence)](tcg-integration.md).
