# Add a card game (licence)

Card Vault supports several card games ("licences"). Each one plugs a
third-party card database into the unified API. Card data is fetched live, no
catalogue is stored in the database.

## How a licence is wired

```
HTTP request
  → API Platform operation (#[ApiResource] on the DTO)
  → State provider (src/State/)
  → LicenceClientRegistry::get($slug)
  → {Licence}Client  → upstream API
  → {Licence}Normaliser → DTO
```

Clients are tagged with `app.licence_client` and resolved by slug through a
service locator. Adding a licence therefore requires **no change to the
providers, the registry or any dispatch table**: only the new client declares
itself, through its tag.

## Endpoint contract

| Endpoint                                           | Returns                    |
|----------------------------------------------------|----------------------------|
| `GET /api/licence`                                 | List of supported licences |
| `GET /api/licence/{slug}/extensions`               | Extensions of a game       |
| `GET /api/licence/{slug}/extensions/{setId}/cards` | Cards of an extension      |
| `GET /api/licence/{slug}/cards/{cardId}`           | A single card              |

Responses are JSON-LD by default; plain JSON is available through content
negotiation.

## Client contract

Every licence implements `LicenceClientInterface`:

```php
public function listExtensions(): array;               // Extension[]
public function listCards(string $extensionId): array; // Card[]
public function getCard(string $cardId): Card;
```

`getCard()` takes no extension id: upstream card ids are self-sufficient on
both TCGdex (`swsh3-136` encodes the set) and Scryfall (UUID lookup).

## DTO shapes

Plain readonly classes in `apps/api/src/Service/Licence/Dto/`, no Doctrine
mapping. Keep the shape identical across licences so the frontend never
branches on the source game.

**`Licence`**: `slug`, `name`. Static list, read from
`apps/api/resources/licences.json`.

**`Extension`**: `id`, `name`, `totalCards` (nullable).

**`Card`**:

| Field           | Type                      | Notes                                                     |
|-----------------|---------------------------|-----------------------------------------------------------|
| `licence`       | `string`                  | the slug                                                  |
| `cardId`        | `string`                  | prefixed by licence: `pokemon-base1-1`, `magic-{uuid}`    |
| `cardNumber`    | `string`                  | number printed on the card                                |
| `cardName`      | `string`                  |                                                           |
| `extensionId`   | `string`                  | comes from the set, never from the card id                |
| `extensionName` | `string`                  |                                                           |
| `illustrator`   | `?string`                 |                                                           |
| `rarity`        | `?string`                 |                                                           |
| `cardImage`     | `?string`                 |                                                           |
| `variant`       | `string[]`                | names of the variants the card exists in                  |
| `prices`        | `array<string, PriceSet>` | keyed by variant name, empty when the source exposes none |

**`PriceSet`**: `avg`, `low`, `trend`, floats in EUR, `0.0` when the source
knows the variant but not its price. USD is out of scope, see #53.

## Identifiers and links

`cardId` is the public identifier and is prefixed to stay unique across
licences. The single card route accepts that prefixed form and strips the
prefix before calling upstream, so a `cardId` read from a list can be used
as-is.

Extensions are not addressable individually: no such endpoint exists, so they
carry anonymous `genid` IRIs instead of real URLs.

## Error contract

| Situation                       | Exception                       | HTTP |
|---------------------------------|---------------------------------|------|
| Unknown slug, extension or card | `LicenceNotFoundException`      | 404  |
| Upstream 5xx or network failure | `UpstreamNotAvailableException` | 502  |

Mapped in `config/packages/api_platform.yaml` under `exception_to_status`.

Upstream outages are detected by `UpstreamAwareHttpClient`, a PSR-18
decorator. It converts 5xx responses and transport failures into
`UpstreamNotAvailableException`, and lets 4xx through untouched so a missing
card stays a 404. Every licence must wrap its HTTP client in it.

## Steps to add a licence

1. Create `apps/api/src/Service/Licence/{Licence}/` with a client implementing
   `LicenceClientInterface` and a normaliser turning upstream payloads into the
   DTOs above.
2. Tag the client:
   `#[AutoconfigureTag('app.licence_client', ['slug' => '{slug}'])]`.
3. Wrap the HTTP client in `UpstreamAwareHttpClient`. If the upstream ships an
   SDK needing setup before injection, add a factory next to the client and
   register it in `config/services.yaml` (see `TcgdexFactory`).
4. Add the licence to `apps/api/resources/licences.json`.
5. Update the [frontend licence filter](frontend.md) if it hardcodes games, and
   the supported-games table in the [user guide](../user-guide/usage.md).

Nothing else: routes, serialisation and OpenAPI documentation come from the
shared providers.

## Upstream notes

| Licence | Source                | Client                                     |
|---------|-----------------------|--------------------------------------------|
| Pokémon | TCGdex v2, no API key | official `tcgdex/sdk` package              |
| Magic   | Scryfall, no API key  | hand-rolled, Scryfall publishes no PHP SDK |

The v1 project (`card_vault_v1`, a separate Python/FastAPI codebase) holds the
same two integrations. Useful only to read upstream payload shapes, not as a
template: different language, no shared interface, and known bugs.

## Tests

- Unit test the normaliser against a payload captured from the real upstream.
- Unit test the client with `MockHttpClient`: nominal case, unknown card
  (upstream 404 gives `LicenceNotFoundException`), outage (upstream 5xx gives
  `UpstreamNotAvailableException`).
- Unit test the registry: known slug, unknown slug.
- Smoke test the live upstream by hand before opening a PR.

See [testing conventions](../contributing/guidelines.md).

## Known limitations

| Limitation                                                 | Ticket               |
|------------------------------------------------------------|----------------------|
| `listCards()` makes one upstream call per card             | #29, catalogue cache |
| Magic double-faced cards expose the first face only        | #54                  |
| Prices are EUR only                                        | #53                  |
| Scryfall calls send no User-Agent and are not rate-limited | #52                  |
