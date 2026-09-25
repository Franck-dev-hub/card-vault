# Card Vault

Card Vault tracks the trading cards a user owns, across several card games, and
recognises a card from a photo.\
This file is the glossary: one word per concept, and the word used here wins.

## Catalogue

**Game**:\
A trading card game supported by Card Vault, such as Pokémon or Magic: The
Gathering.\
Never a match being played: Card Vault does not track matches.\
_Avoid_: licence, franchise, TCG

**Extension**:\
A release of cards within a game.\
_Avoid_: set, series, expansion

**Card**:\
A card as printed, shared by every user.\
_Avoid_: item

**Variant**:\
A print finish of a card, such as normal, reverse holo or foil.\
_Avoid_: finish, version

## Ownership

**Vault**:\
All the copies one user owns, across every game.\
A user has exactly one.\
_Avoid_: collection, library

**Copy**:\
One physical card a user owns, in a given variant, condition and language.\
_Avoid_: item, collection item, entry

**Condition**:\
The physical state of a copy, from Mint to Poor.\
_Avoid_: grade, which means a professional grading score

**Inventory**:\
A physical storage place, such as a binder or a box, holding copies from the
vault, of any game.\
A copy sits in one inventory at most, or in none.\
_Avoid_: binder, box, list

**Deck**:\
A list of cards from a single game, with quantities, built to play.\
It holds cards, not copies, so it can include cards the user does not own.\
_Avoid_: list

## Recognition

**Scan**:\
Recognising a card from a photo taken with the camera.\
_Avoid_: prediction, classification

**Match**:\
A card proposed by a scan, with its similarity score; the user confirms one.\
_Avoid_: prediction, result
