# Roadmap

V1.0 is the MVP.\
Each minor version after it is a themed update.

## Path to V1.0

V1.0 is the first public release on prod.\
Everything before it ships to preprod.

Each milestone is dependency-closed: no ticket in one depends on a ticket in a
later one, so they can be taken in order.\
The next thing to work on is always the lowest milestone still holding open
tickets.

| Milestone | Delivers                                               | Target  |
|-----------|--------------------------------------------------------|---------|
| V0.1      | Backend foundation: Doctrine, migrations, API Platform | done    |
| V0.2      | Catalogue read path, split Redis, non-blocking ML      | done    |
| V0.2.1    | ML closed to the public, dead CORS removed, CI guards  | preprod |
| V0.3      | Register, log in, log out                              | preprod |
| V0.4      | Browse game, extension, card and its detail            | preprod |
| V0.5      | Add and remove cards from the vault                    | preprod |
| V0.6      | Scan a card with the camera                            | preprod |
| V0.7      | E2E, backups, rate limiting, legal pages, consent      | preprod |
| V1.0      | Public launch                                          | prod    |

## Planned

Product scope only.\
Infrastructure, observability and compliance tickets use the same milestones but
are not listed here.

### V1.0

#### Account

- Create an account
- Log in to your account
- Log out from your account

#### Card display

- Browse available games
- Browse their related extensions
- Browse all cards within an extension
- View detailed information for each card

#### Vault

- Add a card to your vault
- Add a card variant to your vault
- Remove a card from your vault
- Scan a card to add it to your vault

### V1.1: AI update

- Add bulk card scanning

### V1.2: Card details update

- Display card prices
- Change card language
- Update card condition (Near Mint, Mint, Excellent, Good, Lightly Played,
  Played, Poor)

### V1.3: Value update

- Edit a card's purchase price
- Display vault value
- Display game value
- Display extension value

### V1.4: Stats, search and community update

- Stats
    - Add global statistics to the homepage
    - Add statistics to the vault page
    - Add extension-specific statistics to extension pages
- Search (filters and sorting)
    - Improve filtering and sorting for the vault
    - Improve filtering and sorting for Extensions
    - Improve filtering and sorting for Cards
- Community
    - Launch a community Discord server
    - Add a Discord support link
    - Set up a donation link for financial support

### V1.5: Inventory and deck update

- Inventory
    - Create an inventory
    - Delete an inventory
    - Add cards to an inventory
    - Remove cards from an inventory
    - Enable filtering and sorting within the inventory
- Deck
    - Create a deck
    - Delete a deck
    - Add cards to a deck
    - Remove cards from a deck
    - Enable filtering and sorting within decks

### V1.6: View and vault update

- View
    - Change vault view (rows or grid)
    - Change extension view (rows or grid)
    - Change cards view (rows or grid)
    - Implement dark mode
- Account
    - Delete the account (never used, but kept just in case)
    - Change username
    - Change currency
    - Change password
    - Change web app language

### V1.7: Progression update

- Add themes (base colour template)
- Display vault progression
- Display game progression
- Display extension progression

### V1.8: Multi-select update

- Personalised theme (full colour customisation with a main and secondary
  colour)
- Inventory
    - Multi-select to add a card
    - Multi-select to delete a card
    - Multi-select to move a card
- Deck
    - Multi-select to add a card
    - Multi-select to delete a card
    - Multi-select to move a card

### V1.9: Import / export update

- Import
    - Full user data
    - The vault
    - An inventory
    - A deck
- Export
    - Full user data
    - The vault
    - An inventory
    - A deck

### V1.10: Auth update

- Verify email address on registration
- Recover a forgotten password
- Sign in with Google and sync the account
- Two-factor authentication (TOTP)

### V1.11: Friends update

- Add a friend
- Delete a friend
- Compare stats with friends

### V1.12: Gamification update

- Push notifications
- Gamification (badges)

## Out of scope

### For now

- ONNX Runtime: model export planned, deferred until camera scan becomes a
  bottleneck.

### Completely out of scope

- Real money transactions
- Real-time chat
