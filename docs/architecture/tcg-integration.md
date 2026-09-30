# Add a game

Card data comes from the sources and is cached, never stored: see
[ADR 0004](../adr/0004-live-card-data.md).

```
request → API Platform operation (#[ApiResource] on the DTO)
        → state provider (src/State/)
        → GameClientRegistry::get($slug)
        → {Game}Client → upstream → {Game}Normaliser → DTO
```

Clients declare themselves through a tag, so adding a game touches no provider,
no registry and no routing.

## Endpoints

Listed in the [backend doc](backend.md#endpoints).\
JSON-LD by default, plain JSON through content negotiation.

## DTOs

Plain readonly classes in `apps/api/src/Service/Game/Dto/`, no Doctrine
mapping, identical across games so the frontend never branches on the game.\
Their fields are in the OpenAPI documentation.

`cardId` is prefixed (`pokemon-base1-1`, `magic-{uuid}`) to stay unique across
games.\
The single card route accepts that form and strips the prefix before calling
upstream, so a `cardId` read from a list is usable as is.

Extensions have no single-item route, so they carry anonymous `genid` IRIs.

A `PriceSet` exists only when the source has a price, hence `avg` is always
set.\
`low` and `trend` are `null` when the source does not track them at all
(Scryfall) and `0.0` when it does but has no value for that card (Cardmarket).

## Errors

| Situation                       | Exception                       | HTTP |
|---------------------------------|---------------------------------|------|
| Unknown slug, extension or card | `GameNotFoundException`         | 404  |
| Upstream 5xx or network failure | `UpstreamNotAvailableException` | 502  |

Mapped in `config/packages/api_platform.yaml` under `exception_to_status`.\
Detection depends on the transport:

- Symfony `HttpClientInterface`: `toArray()` throws already, catch
  `ClientExceptionInterface` (4xx) then `ExceptionInterface` (the rest).
- An SDK that swallows status codes (TCGdex): wrap its PSR-18 client in
  `UpstreamAwareHttpClient`.

Beware the homonym: Symfony's `ClientExceptionInterface` means 4xx, the PSR-18
one means any client failure.

## Adding a game

Implement `GameClientInterface`:

```php
public function listExtensions(): array;               // Extension[]
public function listCards(string $extensionId): array; // Card[]
public function getCard(string $cardId): Card;         // upstream ids are self-sufficient
```

1. Create `src/Service/Game/{Game}/` with a client and a normaliser.
2. Tag the client with
   `#[AutoconfigureTag('app.game_client', ['slug' => '{slug}'])]`.
3. Surface outages as `UpstreamNotAvailableException`, see above.\
   Declare a scoped client under `framework.http_client.scoped_clients` for a
   plain HTTP source (`scryfall.client`, injected as `$scryfallClient`), or a
   factory for an SDK (`TcgdexFactory`).
4. Add the game to `apps/api/resources/games.json`.
5. Update the games table in the [README](../../README.md#features).

Routes, serialisation and OpenAPI documentation come from the shared providers.

Test the normaliser against a payload captured upstream, and the client with
`MockHttpClient`: nominal case, upstream 404, upstream 5xx.

## Sources

| Game    | Source            | Client                | Cost of one extension                      |
|---------|-------------------|-----------------------|--------------------------------------------|
| Pokémon | TCGdex v2, no key | `tcgdex/sdk`          | one call per card                          |
| Magic   | Scryfall, no key  | Symfony scoped client | one call per 175 cards, follow `next_page` |

## Known limitations

| Limitation                                                 | Ticket               |
|------------------------------------------------------------|----------------------|
| `listCards()` costs one call per card on Pokémon           | #29, catalogue cache |
| Magic double-faced cards expose the front face only        | #54                  |
| Prices are EUR only                                        | #53                  |
| Scryfall calls send no User-Agent and are not rate-limited | #52                  |
