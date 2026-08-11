# Tickets Card Vault

Tableau de travail temporaire, à supprimer après création des issues GitHub.
Source: roadmap V1.x (`docs/contributing/roadmap.md`) + inventaire du legacy
(`card_vault_v1`) + analyse des manques pour une web app en production.

## Phase 1 — Fondations backend (bloquant)

| ID | Ticket | Section | Priorité | Roadmap |
|----|--------|---------|----------|---------|
| 1 | Scaffold Doctrine ORM + migrations (config, première entité, tests de mapping) | backend | Critique | — |
| 2 | Installer et configurer API Platform (resources, DTOs, OpenAPI) | backend | Critique | — |
| 3 | Auth par session-cookie: register, login, logout, /me, Voters, protection CSRF | backend | Critique | V1.0 Account |
| 4 | Entités License, Extension, Card + migration | backend | Haute | V1.0 Card display |
| 5 | Intégrations TCG: services TCGDex (Pokémon) + Scryfall (Magic), interface + normalisation | backend | Haute | V1.0 Card display |

## Phase 2 — Card display (V1.0)

| ID | Ticket | Section | Priorité | Roadmap |
|----|--------|---------|----------|---------|
| 6 | Endpoints /api/license: list, extensions, cards, card detail, pagination, stratégie image | backend | Haute | V1.0 Card display |
| 7 | Core frontend: HTTP service withCredentials + auth guard + lazy routes | frontend | Haute | — |
| 8 | Feature Auth frontend: login, register, logout | frontend | Haute | V1.0 Account |
| 9 | Feature Card display: browse license → extensions → cartes → détail | frontend | Haute | V1.0 Card display |

## Phase 3 — Recherche et navigation

| ID | Ticket | Section | Priorité | Roadmap |
|----|--------|---------|----------|---------|
| 10 | Recherche textuelle de cartes par nom (backend + frontend) | backend + frontend | Haute | V1.4 Research |
| 11 | Pagination transverse des listes (catalogue + vault) | backend | Haute | — |

## Phase 4 — Collection (V1.0)

| ID | Ticket | Section | Priorité | Roadmap |
|----|--------|---------|----------|---------|
| 12 | Entité Collection + endpoints /api/vault CRUD (variants normal/reverse/holo unifiés) | backend | Haute | V1.0 Collection |
| 13 | Endpoints /api/vault/stats et /api/vault/recent | backend | Moyenne | V1.4 Stats |
| 14 | Feature Collection frontend: page vault, ajout/retrait par variant | frontend | Haute | V1.0 Collection |

## Phase 5 — Scan + ML (V1.0 / V1.1)

| ID | Ticket | Section | Priorité | Roadmap |
|----|--------|---------|----------|---------|
| 15 | Proxy /api/scan backend → ML service | backend | Haute | V1.0 Scan |
| 16 | Feature Scan frontend: caméra + POST /api/scan | frontend | Haute | V1.0 Scan |
| 17 | Bulk scanning: index par lot, plusieurs cartes par photo | ml | Moyenne | V1.1 AI update |

## Phase 6 — Parité legacy

| ID | Ticket | Section | Priorité | Roadmap |
|----|--------|---------|----------|---------|
| 18 | Page Stats frontend: stats + % de complétion par licence | frontend | Moyenne | V1.4 Stats |
| 19 | Feature Dashboard: stats grid, recent, license breakdown | frontend | Moyenne | V1.4 Stats |
| 20 | Dark mode (thème clair/sombre persisté) | frontend | Moyenne | V1.6 View |
| 21 | Settings/Profile: email, password, delete account | backend + frontend | Moyenne | V1.6 Account |
| 22 | Pages légales statiques (terms, privacy, cookies, FAQ, contacts, about) | frontend | Moyenne | — |

## Phase 7 — Sécurité, email et production

| ID | Ticket | Section | Priorité | Roadmap |
|----|--------|---------|----------|---------|
| 23 | Rate limiting sur /api/login (anti brute-force) | infra | Critique | — |
| 24 | Infrastructure mailer prod (DSN, templates, envoi) | backend/infra | Haute | — |
| 25 | Recovery password: forgot + reset par email | backend + frontend | Haute | V1.10 Auth |
| 26 | Vérification email à l'inscription | backend | Haute | V1.10 Auth |
| 27 | Backups base de données (pg_dump/restore) | infra | Haute | — |
| 28 | Cache catalogue TCG + images | backend | Moyenne | — |
| 29 | Export de données RGPD (data portability) | backend + frontend | Moyenne | V1.9 Import/export |
| 30 | i18n (fr/en): décision de framework, impacte toute l'UI | frontend | Moyenne | V1.6 Account |

## Phase 8 — Qualité et admin

| ID | Ticket | Section | Priorité | Roadmap |
|----|--------|---------|----------|---------|
| 31 | Playwright E2E: setup + chemins critiques (auth, browse, collection) | frontend/infra | Haute | — |
| 32 | EasyAdmin: panneau admin CRUD | backend | Moyenne | — |
| 33 | Sentry: SDK backend + frontend | infra/backend/frontend | Moyenne | — |

## Phase 9 — Production, backlog

| ID | Ticket | Section | Priorité | Roadmap |
|----|--------|---------|----------|---------|
| 34 | Angular 21 → 22 | frontend | Basse | — |
| 35 | Matomo + Matomo Tag Manager | infra/frontend | Basse | — |
| 36 | Tarteaucitron (consentement cookies) | frontend | Basse | — |
| 37 | Cloudflare Turnstile (anti-bot) | backend/frontend | Basse | V1.10 Auth |

## Ordre de priorité global

1. Tickets 1→11: fondations, card display, recherche, pagination (V1.0)
2. Tickets 12→17: collection + scan (V1.0)
3. Tickets 18→22: parité avec la dernière version du legacy
4. Tickets 23→30: sécurité, email, production (rate limiting et pagination sont critiques, perdus de la v1)
5. Tickets 31→33: qualité et admin
6. Tickets 34→37: backlog production
