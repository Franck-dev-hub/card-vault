---
status: accepted
---

# Fetch card data live, store none of it

Games, extensions and cards come from the upstream APIs (TCGdex, Scryfall) on
every request; nothing from the catalogue is stored in the database.\
The catalogue DTOs only carry data, search by name is served upstream, and the
card grid only needs images, so no local catalogue table pays for itself.\
Copies in a vault keep a snapshot of the card (name, image) instead of a foreign
key, so a vault stays intact when upstream renames or removes a card.

## Consequences

- Upstream calls are the cost to watch: the catalogue cache (#29) cuts them
  without storing the catalogue.
- Extensions are the only open case: a small local table may beat the cache once
  #29 is measured (#5).
