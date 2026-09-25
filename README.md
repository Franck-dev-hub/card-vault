<div id="welcome" align="center">
  <img src="apps/frontend/src/assets/brand_logo.svg" alt="Card Vault logo" width="300px"/>
</div>

Card Vault keeps all your trading cards in one vault, whatever the game.\
Search for a card or point your camera at it, and it lands in your vault.

---

<div align="center">

[![Website](https://img.shields.io/website?url=https://card-vault.fr&label=card-vault.fr)](http://card-vault.fr)
[![GitHub License](https://img.shields.io/github/license/Franck-dev-hub/card-vault?label=License)](https://github.com/Franck-dev-hub/card-vault/blob/prod/LICENSE)
[![GitHub Issues](https://img.shields.io/github/issues/Franck-dev-hub/card-vault?&logo=github?&label=Issues)](https://github.com/Franck-dev-hub/card-vault/issues)

![Github CI](https://github.com/Franck-dev-hub/card-vault/actions/workflows/ci.yaml/badge.svg)
![Release](https://img.shields.io/github/v/release/Franck-dev-hub/card-vault?label=Last%20release)
![Version](https://img.shields.io/github/v/tag/Franck-dev-hub/card-vault?label=Last%20tag&color=blue)

![Pokémon](https://img.shields.io/badge/TCG-Pokémon-FFCB05)
![Magic](https://img.shields.io/badge/TCG-Magic%20the%20gathering-D02E20)

</div>

---

## Table of contents
- [Welcome](#welcome)
- [Features](#features)
- [Technologies used](#technologies-used)
- [Installation](#installation)
- [Contributing and security](#contributing-and-security)
- [Authors](#authors)
- [Community](#community)

---

## Features

Status: pre-release.\
The features below are the V1.0 scope, see the
[roadmap](docs/contributing/roadmap.md) for what lands when.

| V1.0 scope                                               | Games                                                                                                                         |
|----------------------------------------------------------|-------------------------------------------------------------------------------------------------------------------------------|
| Manually add cards to your vault using search            | ![Pokémon](https://img.shields.io/badge/Pokémon-FFCB05) ![Magic](https://img.shields.io/badge/Magic%20the%20gathering-D02E20) |
| Add cards to your vault by scanning them with a camera   | ![Pokémon](https://img.shields.io/badge/Pokémon-FFCB05)                                                                       |

### Upcoming
- Adding ![Lorcana](https://img.shields.io/badge/Lorcana-E6DBB9)
  ![YuGiOh](https://img.shields.io/badge/Yu%20Gi%20Oh!-FFD700)
  ![One Piece](https://img.shields.io/badge/One%20Piece-E74C3C)
  ![Palworld](https://img.shields.io/badge/Palworld-008080)
- Adding inventory and deck building
- Adding statistics and vault value estimation
- Improve core code (codebase, CI/CD, Docker ...)

---

## Technologies used
| Part             | Language / framework                                                                                                                                                                   | Tools                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      | Tests                                                                                                                                                                                                                                                                                                                                                     |
|------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| Backend          | ![Symfony](https://img.shields.io/badge/PHP-Symfony-black?logo=symfony&logoColor=fff&labelColor=777BB3)                                                                                | ![API Platform](https://img.shields.io/badge/API_Platform-6366F1?logo=api-platform&logoColor=white) ![Doctrine](https://img.shields.io/badge/Doctrine-4479A1?logo=doctrine&logoColor=white) ![EasyAdmin](https://img.shields.io/badge/EasyAdmin-1B2A4A?logo=symfony&logoColor=white)                                                                                                                                                                                                                                                                                                                                                                                       | ![PHPStan](https://img.shields.io/badge/PHPStan-93C748?logo=php&logoColor=white) ![PHPUnit](https://img.shields.io/badge/PHPUnit-3A4A5C?logo=php&logoColor=white) ![PHP CS Fixer](https://img.shields.io/badge/PHP%20CS%20Fixer-0066C8) ![Rector](https://img.shields.io/badge/Rector-AA2FF3) ![Infection](https://img.shields.io/badge/Infection-D33A2C) |
| Frontend         | ![Angular](https://img.shields.io/badge/TS-Angular-DD0031?logo=angular&logoColor=fff&labelColor=3178C6)                                                                                |                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            | ![Vitest](https://img.shields.io/badge/Vitest-6E9F18?logo=vitest&logoColor=white) ![Playwright](https://img.shields.io/badge/Playwright-2EAD33?logo=playwright&logoColor=white)                                                                                                                                                                           |
| Machine learning | ![Python](https://img.shields.io/badge/Python-3776AB?logo=python&logoColor=fff)                                                                                                        | ![PyTorch](https://img.shields.io/badge/PyTorch-ee4c2c?logo=pytorch&logoColor=white) ![Hugging Face](https://img.shields.io/badge/Hugging%20Face-FFD21E?logo=huggingface&logoColor=000) ![FAISS](https://img.shields.io/badge/FAISS-4285F4?logo=meta&logoColor=white)                                                                                                                                                                                                                                                                                                                                                                                                      |                                                                                                                                                                                                                                                                                                                                                           |
| Database         | ![Postgres](https://img.shields.io/badge/Postgres-%23316192.svg?logo=postgresql&logoColor=white) ![Redis](https://img.shields.io/badge/Redis-%23DD0031.svg?logo=redis&logoColor=white) | ![pgAdmin](https://img.shields.io/badge/pgAdmin-316192?logo=postgresql&logoColor=white) ![RedisInsight](https://img.shields.io/badge/RedisInsight-DC382D?logo=redis&logoColor=white) ![Mailpit](https://img.shields.io/badge/Mailpit-3399FF?logo=minutemailer&logoColor=white)                                                                                                                                                                                                                                                                                                                                                                                             |                                                                                                                                                                                                                                                                                                                                                           |
| DevOps           |                                                                                                                                                                                        | ![Docker Compose](https://img.shields.io/badge/Docker-Compose-gray?logo=docker&logoColor=fff&labelColor=2496ED) ![GitHub Actions](https://img.shields.io/badge/GitHub_Actions-2088FF?logo=github-actions&logoColor=white) ![Caddy](https://img.shields.io/badge/Caddy-1F8AC8?logo=caddy&logoColor=white) ![Sentry](https://img.shields.io/badge/Sentry-362D59?logo=sentry&logoColor=white) ![Matomo](https://img.shields.io/badge/Matomo-3152A0?logo=matomo&logoColor=white) ![Tarteaucitron](https://img.shields.io/badge/Tarteaucitron-F7D917?logo=tarteaucitron&logoColor=black) ![FrankenPHP](https://img.shields.io/badge/FrankenPHP-b3d133?logo=php&logoColor=black) |                                                                                                                                                                                                                                                                                                                                                           |

---

## Installation

Requires Docker with Docker Compose, [Castor](https://castor.jolicode.com) and
Git.

```bash
git clone https://github.com/Franck-dev-hub/card-vault.git
cd card-vault
castor setup:env      # generate the gitignored local secrets
castor docker:build   # build and start the dev stack
```

The app is then served at http://card-vault.localhost, the API docs at
http://card-vault.localhost/api/docs.

Full setup, service URLs, test and lint commands:
[Installation](docs/getting-started/installation.md).

---

## Contributing and security
- Please read our
  [![Contributing](https://img.shields.io/badge/Contributing-Guidelines-blue?logo=git&logoColor=white)](CONTRIBUTING.md)
- See our
  [![Roadmap](https://img.shields.io/badge/Roadmap-Plan-informational?logo=github&logoColor=white)](docs/contributing/roadmap.md)
- Bug reports and feature requests are welcome via
  [![GitHub issues](https://img.shields.io/badge/GitHub%20issues-121013?logo=github&logoColor=white)](https://github.com/Franck-dev-hub/card-vault/issues)
- Found a security issue? Please follow our
  [![Security Policy](https://img.shields.io/badge/Security-Policy-informational?logo=awesomelists&logoColor=white)](SECURITY.md)
  instead of opening a public issue.
- The code is AGPL-3.0, the Card Vault name and logo are not: see the
  [trademark policy](TRADEMARK.md).

---

## Authors
| Name                | Github                                                                                                                        | Linkedin                                                                                                                                                              |
|---------------------|-------------------------------------------------------------------------------------------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| **Franck Spadotto** | [![GitHub](https://img.shields.io/badge/GitHub-%23121011.svg?logo=github&logoColor=white)](https://github.com/Franck-dev-hub) | [![LinkedIn](https://custom-icon-badges.demolab.com/badge/LinkedIn-0A66C2?logo=linkedin-white&logoColor=fff)](https://www.linkedin.com/in/franck-spadotto-466bb1369/) |
| **Jeremy Laurens**  | [![GitHub](https://img.shields.io/badge/GitHub-%23121011.svg?logo=github&logoColor=white)](https://github.com/JeremyLrs)      | [![LinkedIn](https://custom-icon-badges.demolab.com/badge/LinkedIn-0A66C2?logo=linkedin-white&logoColor=fff)](https://www.linkedin.com/in/jeremylrs/)                 |

---

## Community
- Join our community
  [![Discord](https://img.shields.io/badge/Discord-Join-5865F2?logo=discord&logoColor=white)](https://discord.com)
- Or help keep the app running
  [![Ko-fi](https://img.shields.io/badge/Ko--fi-Support%20Us-FF5E5B?&logo=ko-fi&logoColor=white)](https://ko-fi.com/cardvault)
