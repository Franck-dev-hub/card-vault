# Architecture overview

## System

- Card Vault is a monorepo with four deployable pieces orchestrated by Docker
  Compose.
- The frontend only talks to the API.\
  The API talks to the ML service, the TCG APIs, PostgreSQL and the two Redis
  instances.
- The ML service calls the API back to enrich its matches with card details.

```mermaid
flowchart LR
    User[Browser] --> Caddy[Caddy proxy]
    Caddy --> API[API: Symfony + API Platform]
    Caddy --> FE[Frontend: Angular, static]
    API --> PG[(PostgreSQL)]
    API --> RC[(redis-cache)]
    API --> RS[(redis-session)]
    API --> ML[ML: FastAPI, DINOv2 + FAISS]
    ML -. card details .-> API
    API --> TCG[TCG APIs: Pokemon TCGDex, Scryfall]
```

## Layers

| Layer    | Home                       | Role                                                  |
|----------|----------------------------|-------------------------------------------------------|
| API      | [backend.md](backend.md)   | REST API, auth, vault domain, integration hub         |
| Frontend | [frontend.md](frontend.md) | SPA, standalone components, lazy-loaded routes        |
| ML       | [ml.md](ml.md)             | card recognition by image similarity (DINOv2 + FAISS) |
| Proxy    | [proxy.md](proxy.md)       | auto-HTTPS, routing, security headers                 |

## Stack

### Backend

![Symfony](https://img.shields.io/badge/PHP-Symfony-black?logo=symfony&logoColor=fff&labelColor=777BB3)
![API Platform](https://img.shields.io/badge/API_Platform-6366F1?logo=api-platform&logoColor=white)
![Doctrine](https://img.shields.io/badge/Doctrine-4479A1?logo=doctrine&logoColor=white)
![FrankenPHP](https://img.shields.io/badge/FrankenPHP-b3d133?logo=php&logoColor=black)
![EasyAdmin](https://img.shields.io/badge/EasyAdmin-1B2A4A?logo=symfony&logoColor=white)

Tests and checks:

![PHPUnit](https://img.shields.io/badge/PHPUnit-3A4A5C?logo=php&logoColor=white)
![Infection](https://img.shields.io/badge/Infection-D33A2C)
![PHPStan](https://img.shields.io/badge/PHPStan-93C748?logo=php&logoColor=white)
![PHP CS Fixer](https://img.shields.io/badge/PHP%20CS%20Fixer-0066C8)
![Rector](https://img.shields.io/badge/Rector-AA2FF3)
![k6](https://img.shields.io/badge/k6-7D64FF?logo=k6&logoColor=white)

### Frontend

![Angular](https://img.shields.io/badge/TS-Angular-DD0031?logo=angular&logoColor=fff&labelColor=3178C6)
![Tarteaucitron](https://img.shields.io/badge/Tarteaucitron-F7D917?logo=tarteaucitron&logoColor=black)

Tests and checks:

![Vitest](https://img.shields.io/badge/Vitest-6E9F18?logo=vitest&logoColor=white)
![Playwright](https://img.shields.io/badge/Playwright-2EAD33?logo=playwright&logoColor=white)
![Lighthouse CI](https://img.shields.io/badge/Lighthouse_CI-F44B21?logo=lighthouse&logoColor=white)

### Machine learning

![Python](https://img.shields.io/badge/Python-3776AB?logo=python&logoColor=fff)
![PyTorch](https://img.shields.io/badge/PyTorch-ee4c2c?logo=pytorch&logoColor=white)
![Hugging Face](https://img.shields.io/badge/Hugging%20Face-FFD21E?logo=huggingface&logoColor=000)
![FAISS](https://img.shields.io/badge/FAISS-4285F4?logo=meta&logoColor=white)
![uv](https://img.shields.io/badge/uv-DE5FE9?logo=uv&logoColor=white)

Tests and checks:

![pytest](https://img.shields.io/badge/pytest-0A9EDC?logo=pytest&logoColor=white)
![Ruff](https://img.shields.io/badge/Ruff-D7FF64?logo=ruff&logoColor=black)
![mypy](https://img.shields.io/badge/mypy-2A6DB2?logo=python&logoColor=white)

### Data

![PostgreSQL and pgAdmin](https://img.shields.io/badge/PostgreSQL-pgAdmin-316192?logo=postgresql&logoColor=white)
![Redis and RedisInsight](https://img.shields.io/badge/Redis-RedisInsight-DC382D?logo=redis&logoColor=white)
![MariaDB](https://img.shields.io/badge/MariaDB-003545?logo=mariadb&logoColor=white)

### Hosting and network

![Docker Compose](https://img.shields.io/badge/Docker-Compose-gray?logo=docker&logoColor=fff&labelColor=2496ED)
![Caddy](https://img.shields.io/badge/Caddy-1F8AC8?logo=caddy&logoColor=white)
![Cloudflare](https://img.shields.io/badge/Cloudflare-F38020?logo=cloudflare&logoColor=white)
![Turnstile](https://img.shields.io/badge/Turnstile-F38020?logo=cloudflare&logoColor=white)
![Tailscale](https://img.shields.io/badge/Tailscale-242424?logo=tailscale&logoColor=white)

### Observability and analytics

![Sentry](https://img.shields.io/badge/Sentry-362D59?logo=sentry&logoColor=white)
![OpenTelemetry](https://img.shields.io/badge/OpenTelemetry-000000?logo=opentelemetry&logoColor=white)
![Grafana](https://img.shields.io/badge/Grafana-F46800?logo=grafana&logoColor=white)
![Tempo](https://img.shields.io/badge/Tempo-F46800?logo=grafana&logoColor=white)
![Matomo](https://img.shields.io/badge/Matomo-3152A0?logo=matomo&logoColor=white)

### Tooling and CI

![GitHub Actions](https://img.shields.io/badge/GitHub_Actions-2088FF?logo=github-actions&logoColor=white)
![Castor](https://img.shields.io/badge/Castor-F7A41D)
![Renovate](https://img.shields.io/badge/Renovate-1A1F6C?logo=renovate&logoColor=white)
![Mailpit](https://img.shields.io/badge/Mailpit-3399FF?logo=minutemailer&logoColor=white)

Checks:

![Hadolint](https://img.shields.io/badge/Hadolint-2496ED?logo=docker&logoColor=white)
![Trivy](https://img.shields.io/badge/Trivy-1904DA?logo=trivy&logoColor=white)
