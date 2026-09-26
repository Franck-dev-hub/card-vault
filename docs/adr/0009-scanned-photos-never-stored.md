---
status: accepted
---

# Never store a scanned photo

A photo sent to `/api/scan` is used to find the card, then forgotten: it is
never written to disk, a database or a log.\
Real phone photos would be the best data to train the model on, but they show
the user's home, hands and surroundings, and the privacy policy promises they
are not kept.\
Training relies on augmented catalogue images and on an evaluation set built
from photos taken on purpose (#86, #87).

## Consequences

- Keeping scans later, even opt-in, means changing the privacy policy (#23) and
  the processing register (#82) first.
- A failed scan cannot be replayed from the server: the user takes the photo
  again.
