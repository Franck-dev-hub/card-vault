<div id="welcome" align="center">
  <img src="apps/frontend/src/assets/brand_logo.svg" alt="Card Vault logo" width="300px"/>
</div>

CardVault is a web application that centralizes and manages collections from all Trading Card Games in a single virtual vault.
The platform offers advanced filtering, cross-game organization, and an integrated AI system capable of recognizing cards directly through the camera, making collection tracking faster, smarter, and seamless.

---

<div align="center">

[![Website](https://img.shields.io/website?url=https://card-vault.fr&label=card-vault.fr)](http://card-vault.fr)
[![GitHub License](https://img.shields.io/github/license/Franck-dev-hub/card-vault?label=License)](https://github.com/Franck-dev-hub/card-vault/blob/prod/LICENSE)
[![Ko-fi](https://img.shields.io/badge/Ko--fi-Support%20Us-FF5E5B?&logo=ko-fi&logoColor=white)](https://ko-fi.com/cardvault)
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
- [Table of contents](#table-of-contents)
- [Features](#features)
- [Technologies used](#technologies-used)
- [Prerequisites](#prerequisites)
- [Installation and run](#installation-and-run)
- [Testing and linting](#testing-and-linting)
- [Contributing and security](#contributing-and-security)
- [Authors](#authors)
- [Contributors](#contributors)
- [Community](#community)

---

## Features
<div style="display: flex; flex-wrap: wrap;">
    <div style="flex: 1; min-width: 300px;">
        <h3>Implemented</h3>
        <ul style="padding-left: 20px;">
            <li>Manually add cards to a collection using search
                <ul>
                    <li><img src="https://img.shields.io/badge/Pokémon-FFCB05" alt="Pokémon" style="vertical-align: middle; margin-right: 8px;"/>
                    <img src="https://img.shields.io/badge/Magic%20the%20gathering-D02E20" alt="Magic the gathering" style="vertical-align: middle; margin-right: 8px;"/></li>
                </ul>
            </li>
            <li>Add cards to a collection by scanning them with a camera</li>
                <ul>
                    <li><img src="https://img.shields.io/badge/Pokémon-FFCB05" alt="Pokémon" style="vertical-align: middle; margin-right: 8px;"/></li>
                </ul>
        </ul>
    </div>
    <div style="flex: 1; min-width: 300px;">
        <h3>Upcoming</h3>
        <ul style="padding-left: 20px;">
<li>Adding <img src="https://img.shields.io/badge/Lorcana-E6DBB9" alt="Lorcana" style="vertical-align: middle; margin-right: 8px;"/> <img src="https://img.shields.io/badge/Yu%20Gi%20Oh!-FFD700" alt="YuGiOh" style="vertical-align: middle; margin-right: 8px;"/> <img src="https://img.shields.io/badge/One%20Piece-E74C3C" alt="One Piece" style="vertical-align: middle; margin-right: 8px;"/> <img src="https://img.shields.io/badge/Palworld-008080" alt="Palworld" style="vertical-align: middle; margin-right: 8px;"/></li>
            <li>Adding inventory and deck building</li>
            <li>Adding statistics and collection value estimation</li>
            <li>Improve core code (codebase, CI/CD, Docker ...)</li>
        </ul>
    </div>
</div>

---

## Technologies used
| Part             | Language / framework                                                                                                                                                                   | Tools                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      | Tests                                                                                                                                                                                                                                   |
|------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| Backend          | ![Symfony](https://img.shields.io/badge/PHP-Symfony-black?logo=symfony&logoColor=fff&labelColor=777BB3)                                                                                | ![API Platform](https://img.shields.io/badge/API_Platform-6366F1?logo=api-platform&logoColor=white) ![Doctrine](https://img.shields.io/badge/Doctrine-4479A1?logo=doctrine&logoColor=white) ![EasyAdmin](https://img.shields.io/badge/EasyAdmin-1B2A4A?logo=symfony&logoColor=white)                                                                                                                                                                                                                                                                                                                                                                                       | ![PHPStan](https://img.shields.io/badge/PHPStan-93C748?logo=php&logoColor=white) ![PHPUnit](https://img.shields.io/badge/PHPUnit-3A4A5C?logo=php&logoColor=white) ![PHP CS Fixer](https://img.shields.io/badge/PHP%20CS%20Fixer-0066C8) |
| Frontend         | ![Angular](https://img.shields.io/badge/Typescript-Angular-DD0031?logo=angular&logoColor=fff&labelColor=3178C6)                                                                        |                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            | ![Vitest](https://img.shields.io/badge/Vitest-6E9F18?logo=vitest&logoColor=white) ![Playwright](https://img.shields.io/badge/Playwright-2EAD33?logo=playwright&logoColor=white)                                                         |
| Machine learning | ![Python](https://img.shields.io/badge/Python-3776AB?logo=python&logoColor=fff)                                                                                                        | ![PyTorch](https://img.shields.io/badge/PyTorch-ee4c2c?logo=pytorch&logoColor=white) ![Hugging Face](https://img.shields.io/badge/Hugging%20Face-FFD21E?logo=huggingface&logoColor=000) ![FAISS](https://img.shields.io/badge/FAISS-4285F4?logo=meta&logoColor=white)                                                                                                                                                                                                                                                                                                                                                                                                      |                                                                                                                                                                                                                                         |
| Database         | ![Postgres](https://img.shields.io/badge/Postgres-%23316192.svg?logo=postgresql&logoColor=white) ![Redis](https://img.shields.io/badge/Redis-%23DD0031.svg?logo=redis&logoColor=white) | ![pgAdmin](https://img.shields.io/badge/pgAdmin-316192?logo=postgresql&logoColor=white) ![RedisInsight](https://img.shields.io/badge/RedisInsight-DC382D?logo=redis&logoColor=white) ![Mailpit](https://img.shields.io/badge/Mailpit-3399FF?logo=minutemailer&logoColor=white)                                                                                                                                                                                                                                                                                                                                                                                             |                                                                                                                                                                                                                                         |
| DevOps           |                                                                                                                                                                                        | ![Docker Compose](https://img.shields.io/badge/Docker-Compose-gray?logo=docker&logoColor=fff&labelColor=2496ED) ![GitHub Actions](https://img.shields.io/badge/GitHub_Actions-2088FF?logo=github-actions&logoColor=white) ![Caddy](https://img.shields.io/badge/Caddy-1F8AC8?logo=caddy&logoColor=white) ![Sentry](https://img.shields.io/badge/Sentry-362D59?logo=sentry&logoColor=white) ![Matomo](https://img.shields.io/badge/Matomo-3152A0?logo=matomo&logoColor=white) ![Tarteaucitron](https://img.shields.io/badge/Tarteaucitron-F7D917?logo=tarteaucitron&logoColor=black) ![FrankenPHP](https://img.shields.io/badge/FrankenPHP-b3d133?logo=php&logoColor=black) |                                                                                                                                                                                                                                         |

---

## Prerequisites
- ![Docker](https://img.shields.io/badge/Docker-2496ED?logo=docker&logoColor=fff) ![Docker Compose](https://img.shields.io/badge/Docker-Compose-gray?logo=docker&logoColor=fff&labelColor=2496ED)
- ![GNU Make](https://img.shields.io/badge/GNU%20Make-A40000?logo=gnu&logoColor=white)
- ![Git](https://img.shields.io/badge/Git-F05032?logo=git&logoColor=fff)

## Installation and run
Clone the repo
```bash
git clone https://github.com/Franck-dev-hub/card-vault.git
cd card-vault
```

Generate the gitignored local environment overrides (secrets, DB credentials, `HF_TOKEN`, ...)
```bash
make env
```

Display `make` help
```bash
make
```

First launch
```bash
make dev/build
```

Start (after build)
```bash
make dev/up
```

Once running, the app is available at:

| Service      | URL                       |
|--------------|---------------------------|
| Frontend     | http://card-vault.localhost          |
| Backend API  | http://card-vault.localhost/api      |
| API docs     | http://card-vault.localhost/api/docs |
| ML service   | http://card-vault.localhost/ml       |
| pgAdmin      | http://localhost:5050     |
| RedisInsight | http://localhost:5540     |

The dev stack exposes mailpit (mail sink) on `http://localhost:8025`.

---

## Testing and linting
Run all linters (PHP CS Fixer + PHPStan, ESLint + TypeScript, ruff + mypy)
```bash
make lint
```

Backend tests (PHPUnit)
```bash
make test/backend
```

Frontend tests (Vitest)
```bash
make test/frontend
```

ML tests (pytest)
```bash
make test/ml
```

E2E tests (Playwright)
```bash
make test/e2e
```

See [Testing and linting](docs/getting-started/installation.md) in the docs for the full command list.

---

## Contributing and security
Please read our [![Contributing](https://img.shields.io/badge/Contributing-Guidelines-blue?logo=git&logoColor=white)](docs/contributing/guidelines.md)
Bug reports and feature requests are welcome via [![GitHub issues](https://img.shields.io/badge/GitHub%20issues-121013?logo=github&logoColor=white)](https://github.com/Franck-dev-hub/card-vault/issues)
Found a security issue ? Please follow our [![Security Policy](https://img.shields.io/badge/Security-Policy-informational?logo=awesomelists&logoColor=white)](SECURITY.md) instead of opening a public issue.

---

## Authors
| Name                | Github                                                                                                                         | Linkedin                                                                                                                                                               |
|---------------------|--------------------------------------------------------------------------------------------------------------------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| **Franck Spadotto** | [![GitHub](https://img.shields.io/badge/GitHub-%23121011.svg?logo=github&logoColor=white)](#https://github.com/Franck-dev-hub) | [![LinkedIn](https://custom-icon-badges.demolab.com/badge/LinkedIn-0A66C2?logo=linkedin-white&logoColor=fff)](#https://www.linkedin.com/in/franck-spadotto-466bb1369/) |
| **Jeremy Laurens**  | [![GitHub](https://img.shields.io/badge/GitHub-%23121011.svg?logo=github&logoColor=white)](#https://github.com/JeremyLrs)      | [![LinkedIn](https://custom-icon-badges.demolab.com/badge/LinkedIn-0A66C2?logo=linkedin-white&logoColor=fff)](#https://www.linkedin.com/in/jeremylrs/)                 |

## Contributors
| Name             | Github                                                                                                                    | Linkedin                                                                                                                                                            |
|------------------|---------------------------------------------------------------------------------------------------------------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------|

---
## Community
Join our community [![Discord](https://img.shields.io/badge/Discord-Join-5865F2?logo=discord&logoColor=white)](https://discord.com)
Or help us to maintain the app [![Ko-fi](https://img.shields.io/badge/Ko--fi-Support%20Us-FF5E5B?&logo=ko-fi&logoColor=white)](https://ko-fi.com/cardvault)
