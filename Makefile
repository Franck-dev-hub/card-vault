.DEFAULT_GOAL := help

# Variables
DC_DEV = docker compose -f docker/compose.yaml -f docker/compose.dev.yaml --env-file .env
DC_PROD = docker compose -f docker/compose.yaml -f docker/compose.prod.yaml --env-file .env.prod
DC_PREPROD = docker compose -f docker/compose.yaml -f docker/compose.preprod.yaml --env-file .env.preprod
DC_CI = docker compose -f docker/compose.yaml -f docker/compose.ci.yaml --env-file .env

# Environments and which ones pull GHCR images before starting
ENVS := dev preprod prod
PULL_ENVS := preprod prod

dev_DC = $(DC_DEV)
preprod_DC = $(DC_PREPROD)
prod_DC = $(DC_PROD)

.PHONY: help env env/prod env/preprod prune
.PHONY: lint lint/frontend lint/backend lint/ml sec sec/frontend sec/backend sec/ml
.PHONY: test test/backend test/frontend test/ml test/e2e migrate migrate-diff ci
.PHONY: release/preprod release/prod

# === ENV ===

# Copy env templates into place (dev + prod + preprod)
env:
	@test -f .env || cp .env.example .env
	@test -f .env.prod || sed 's/^PROJECT_ENV=.*/PROJECT_ENV=prod/' .env.example > .env.prod
	@test -f .env.preprod || sed 's/^PROJECT_ENV=.*/PROJECT_ENV=preprod/' .env.example > .env.preprod
	@echo "Env files ready (.env, .env.prod, .env.preprod). Fill in secrets."

env/prod:
	@test -f .env.prod || sed 's/^PROJECT_ENV=.*/PROJECT_ENV=prod/' .env.example > .env.prod
	@echo ".env.prod created"

env/preprod:
	@test -f .env.preprod || sed 's/^PROJECT_ENV=.*/PROJECT_ENV=preprod/' .env.example > .env.preprod
	@echo ".env.preprod created"

# === ENVIRONMENTS ===

# UP: Start an environment (preprod/prod pull GHCR images first)
%/up:
	$(if $(filter $*,$(ENVS)),,$(error Unknown environment "$*". Valid environments: $(ENVS)))
	$(if $(filter $*,$(PULL_ENVS)),$($*_DC) pull)
	$($*_DC) up -d

# BUILD ALL: Build and start (dev builds locally, preprod/prod pull + start)
%/build:
	$(if $(filter $*,$(ENVS)),,$(error Unknown environment "$*". Valid environments: $(ENVS)))
	$(if $(filter $*,$(PULL_ENVS)),$($*_DC) pull)
	$($*_DC) up --build -d

# BUILD SERVICE: Rebuild/restart a single service (works for ANY service in that env)
define service-build-rule
$(1)/build/%:
	$$($(1)_DC) up --build --no-deps -d $$*
endef
$(foreach env,$(ENVS),$(eval $(call service-build-rule,$(env))))
$(eval .PHONY: $(ENVS:%=%/build/%))

# PULL: Pull GHCR images for an environment
%/pull:
	$(if $(filter $*,$(ENVS)),,$(error Unknown environment "$*". Valid environments: $(ENVS)))
	$($*_DC) pull

# === LINTING ===

lint/frontend:
	$(DC_DEV) run --rm --no-deps frontend sh -c "corepack pnpm install && corepack pnpm run lint && corepack pnpm exec tsc --noEmit"

lint/backend:
	$(DC_CI) run --rm --no-deps php sh -c "composer install --no-interaction --prefer-dist && vendor/bin/phpstan analyse && vendor/bin/php-cs-fixer fix --dry-run --diff"

lint/ml:
	$(DC_DEV) run --rm --no-deps ml sh -c "pip install -q ruff mypy && ruff check . && mypy ."

lint: lint/frontend lint/backend lint/ml

# === SECURITY ===

sec/frontend:
	$(DC_DEV) run --rm --no-deps frontend sh -c "corepack pnpm install && corepack pnpm audit"

sec/backend:
	$(DC_CI) run --rm --no-deps php sh -c "composer install --no-interaction --prefer-dist && composer audit"

sec/ml:
	$(DC_DEV) run --rm --no-deps ml sh -c "pip install -q pip-audit && pip-audit"

sec: sec/frontend sec/backend sec/ml

# === TEST ===

test/backend:
	$(DC_CI) up -d database redis
	$(DC_CI) run --rm php sh -c "composer install --no-interaction --prefer-dist && php bin/phpunit"

test/frontend:
	$(DC_DEV) run --rm --no-deps frontend sh -c "corepack pnpm install && corepack pnpm test"

test/ml:
	$(DC_DEV) run --rm --no-deps ml sh -c "pip install -q pytest && pytest"

test/e2e:
	$(DC_DEV) up -d
	$(DC_CI) --profile e2e run --rm --no-deps playwright sh -c "corepack pnpm install && corepack pnpm run test:e2e"

test: test/backend test/frontend test/ml test/e2e

# === MIGRATIONS ===

migrate:
	$(DC_CI) run --rm --no-deps -e APP_ENV=dev php sh -c "composer install --no-interaction --prefer-dist && php bin/console doctrine:migrations:migrate --no-interaction --all-or-nothing --allow-no-migration"

migrate-diff:
	$(DC_CI) run --rm --no-deps -e APP_ENV=dev php sh -c "composer install --no-interaction --prefer-dist && php bin/console doctrine:migrations:diff"

# === CI ===

# Run the exact same checks as .github/workflows/ci.yaml, locally
ci: lint sec test/backend test/frontend test/ml

# === RELEASES (gitflow) ===

# Feature/fix/chore/doc branches merge into develop.
# When develop is validated: deploy to preprod (preprod branch, same env as prod).
release/preprod:
	gh pr create --base preprod --head develop --title "release: develop -> preprod" --fill

# When preprod is validated: deploy to prod.
release/prod:
	gh pr create --base prod --head preprod --title "release: preprod -> prod" --fill

prune:
	git fetch --prune
	git branch --format '%(refname:short) %(upstream:track)' | awk '$$2 == "[gone]" {print $$1}' | xargs -r git branch -d

# === HELP ===
help:
	@echo " Services : frontend, backend, ml "
	@echo " Environments : dev, preprod, prod "
	@echo ""
	@echo "----- ENVIRONMENTS -----------------------"
	@echo "  env                   -> Create env files"
	@echo "  {env}/up              -> Start an environment"
	@echo "  {env}/build           -> Build + start"
	@echo "  {env}/build/{service} -> Rebuild/restart one service"
	@echo "  {env}/pull            -> Pull GHCR images for that environment"
	@echo ""
	@echo "----- LINTING ---------------------------"
	@echo "  lint           -> Run all linters"
	@echo "  lint/{service} -> ESLint + TypeScript (apps/frontend)"
	@echo ""
	@echo "----- SECURITY ---------------------------"
	@echo "  sec           -> Run all security checks"
	@echo "  sec/{service} -> pnpm audit (apps/frontend)"
	@echo ""
	@echo "----- TEST -------------------------------"
	@echo "  test           -> Run all tests"
	@echo "  test/{service} -> PHPUnit (apps/api)"
	@echo "  test/e2e       -> Playwright (apps/frontend)"
	@echo ""
	@echo "----- MIGRATIONS --------------------------"
	@echo "  migrate       -> Apply pending migrations"
	@echo "  migrate-diff  -> Generate a migration from entity changes"
	@echo ""
	@echo "----- CI ---------------------------------"
	@echo "  ci -> Run the same checks as CI locally"
	@echo ""
	@echo "----- RELEASES ---------------------------"
	@echo "  release/preprod -> Create PR develop → preprod"
	@echo "  release/prod    -> Create PR preprod → prod"
	@echo "  prune           -> Delete merged local branches"
	@echo ""
