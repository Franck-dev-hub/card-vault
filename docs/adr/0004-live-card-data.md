---
status: accepted
---

# Fetch card data live, store none of it

Games, extensions and cards come from the source APIs (TCGdex, Scryfall); the
database stores none of the catalogue.\
Responses are cached instead, at three levels: the browser, the proxy, and a
server-side cache filled on a miss.\
A local copy of the catalogue would cost a sync job and buy nothing the cache
does not: the API only passes this data through, and the sources already serve
search by name.\
A copy in a vault keeps a snapshot of its card (name, image), not a link to a
catalogue row, so the vault stays intact when a source renames or removes a
card.

## Consequences

- The catalogue cache (#29) keeps calls to the sources rare.
- Refreshing only the data that changed, instead of waiting for it to expire,
  is still to be decided (#135).
- Extensions are the only open case: a small local table may beat the cache
  once #29 is measured (#5).
