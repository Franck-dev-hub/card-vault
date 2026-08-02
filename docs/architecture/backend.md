# Backend architecture

## Endpoints (API Platform)

| Method | Path                                         | Auth | Purpose                          |
|--------|----------------------------------------------|------|----------------------------------|
| POST   | /api/register                                | No   | Create an account                |
| POST   | /api/login                                   | No   | Start a session                  |
| POST   | /api/logout                                  | Yes  | Destroy the session              |
| GET    | /api/me                                      | Yes  | Get the connected user           |
| GET    | /api/license                                 | No   | List license                     |
| GET    | /api/license/{slug}/extensions               | No   | List a game's extensions         |
| GET    | /api/license/{slug}/extensions/{setId}/cards | No   | List an extension's cards        |
| GET    | /api/license/{slug}/cards/{cardId}           | No   | Get a single card                |
| GET    | /api/vault                                   | Yes  | List collection items            |
| POST   | /api/vault                                   | Yes  | Add a collection item            |
| PATCH  | /api/vault/{id}                              | Yes  | Update a collection item         |
| DELETE | /api/vault/{id}                              | Yes  | Remove a collection item         |
| GET    | /api/vault/stats                             | Yes  | Collection statistics            |
| GET    | /api/vault/recent                            | Yes  | Recently added items             |
| GET    | /api/dashboard                               | Yes  | Dashboard data                   |
| POST   | /api/scan                                    | Yes  | Proxy an image to the ML service |
| GET    | /api/health                                  | No   | Liveness check                   |

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
