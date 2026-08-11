# Add a card game (licence)

Card Vault supports several card games ("licences"). Each licence plugs a
third-party card database into the unified API through the same pattern:
an external client plus a normaliser.

## Reference legacy implementation

The v1 project has the two reference integrations in `card_vault_v1`:

- Pokémon: `backend/app/services/external/pokemon/` (TCGdex v2, no API key).
- Magic: `backend/app/services/external/magic/` (Scryfall, no API key).

Use them as the template for the next licence (e.g. Yu-Gi-Oh or Lorcana).

## Endpoint contract

A licence must feed every `/api/license` endpoint:

| Endpoint                                           | Returns                    |
|----------------------------------------------------|----------------------------|
| `GET /api/license`                                 | List of supported licences |
| `GET /api/license/{slug}/extensions`               | Extensions of a game       |
| `GET /api/license/{slug}/extensions/{setId}/cards` | Cards of an extension      |
| `GET /api/license/{slug}/cards/{cardId}`           | A single card              |

## Card shape

The normaliser must output a unified card. From the legacy implementation:

- `license`, `card_id` (stable, e.g. `pokemon-base1-1`, `magic-{scryfall-id}`)
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
2. Register the licence in the dispatch map used by `/api/license`.
3. Add the licence to `apps/api/resources/licenses.json`.
4. Update the [frontend licence filter](../architecture/frontend.md) if it
   hardcodes games.
5. Add the licence row to the [backend endpoint doc](../architecture/backend.md)
   and the [user guide](../user-guide/usage.md) supported-games table.

## Tests

- Unit test the normaliser with a fixture payload from the upstream API.
- Unit test the client dispatch map (valid slug, unknown slug, upstream error).
- Manual smoke test against the live upstream before opening a PR.

See [testing conventions](../contributing/guidelines.md).
