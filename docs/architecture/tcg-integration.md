# Add a card game (licence)

Card Vault supports several card games ("licences"). Each licence plugs a
third-party card database into the unified API through the same pattern:
an external client plus a normaliser.

## Reference legacy implementation

The v1 project (`card_vault_v1`, a separate, unrelated Python/FastAPI
codebase, sibling directory to this repo) has two reference integrations:

- Pokémon: `backend/app/services/external/pokemon/` (TCGdex v2, no API key).
- Magic: `backend/app/services/external/magic/` (Scryfall, no API key).

## Endpoint contract

A licence must feed every `/api/licence` endpoint:

| Endpoint                                           | Returns                    |
|----------------------------------------------------|----------------------------|
| `GET /api/licence`                                 | List of supported licences |
| `GET /api/licence/{slug}/extensions`               | Extensions of a game       |
| `GET /api/licence/{slug}/extensions/{setId}/cards` | Cards of an extension      |
| `GET /api/licence/{slug}/cards/{cardId}`           | A single card              |

## Card shape

The normaliser must output a unified card. From the legacy implementation:

- `licence`, `card_id` (stable, e.g. `pokemon-base1-1`, `magic-{scryfall-id}`)
- `card_number`, `card_name`, `extension_id`, `extension_name`
- `illustrator`, `rarity`
- `card_image` (URL, and the medium variant if the source provides one)
- prices when the source exposes them (avg/low/trend)
- `variant` for foil/non-foil distinctions where relevant

Keep the shape identical across licences so the frontend never branches on the
source game.

## Steps to add a licence

1. Create `apps/api/src/Service/Licence/{slug}/` following the legacy layout:
   the client (`{game}_api_services`) and the normaliser (`{game}_standardized`),
   decoupled by an interface.
2. Register the licence in the dispatch map used by `/api/licence`.
3. Add the licence to `apps/api/resources/licences.json`.
4. Update the [frontend licence filter](../architecture/frontend.md) if it
   hardcodes games.
5. Add the licence row to the [backend endpoint doc](../architecture/backend.md)
   and the [user guide](../user-guide/usage.md) supported-games table.

## Tests

- Unit test the normaliser with a fixture payload from the upstream API.
- Unit test the client dispatch map (valid slug, unknown slug, upstream error).
- Manual smoke test against the live upstream before opening a PR.

See [testing conventions](../contributing/guidelines.md).
