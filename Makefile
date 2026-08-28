.DEFAULT_GOAL := help
SHELL := /bin/bash
.SHELLFLAGS := -eu -o pipefail -c

# Variables
# Base .env first, then per-env overrides, gitignored local secrets last
DC_DEV = docker compose -f docker/compose.yaml -f docker/compose.dev.yaml --env-file .env --env-file .env.local
DC_PROD = docker compose -f docker/compose.yaml -f docker/compose.prod.yaml --env-file .env --env-file .env.prod --env-file .env.prod.local
DC_PREPROD = docker compose -f docker/compose.yaml -f docker/compose.preprod.yaml --env-file .env --env-file .env.preprod --env-file .env.preprod.local
DC_CI = docker compose -f docker/compose.yaml -f docker/compose.ci.yaml --env-file .env --env-file .env.local

# Environments and which ones pull GHCR images before starting
ENVS := dev preprod prod
PULL_ENVS := preprod prod
SERVICES := frontend backend ml

dev_DC = $(DC_DEV)
preprod_DC = $(DC_PREPROD)
prod_DC = $(DC_PROD)

.PHONY: help env env/prod env/preprod prune
.PHONY: lint lint/frontend lint/backend lint/ml sec sec/frontend sec/backend sec/ml
.PHONY: test test/backend test/frontend test/ml test/e2e migrate migrate-diff ci
.PHONY: release/preprod release/prod
.PHONY: FORCE

FORCE:

# === ENV ===
# Generate gitignored local overrides.
env:
	@test -f .env.local || { \
		cp .env .env.local; \
		secret=$$(openssl rand -hex 32); \
		password=$$(openssl rand -hex 16); \
		sed -i "s/^APP_SECRET=.*/APP_SECRET=$$secret/; s/^POSTGRES_PASSWORD=.*/POSTGRES_PASSWORD=$$password/; s/^PGADMIN_PASSWORD=.*/PGADMIN_PASSWORD=$$password/" .env.local; \
	}
	@echo ".env.local ready (dev, full copy with generated secrets)."

define generate-env-overrides
	@test -f .env.$(1).local || { \
		{ \
			echo "# $(2) secrets, gitignored, override .env.$(1)"; \
			echo "APP_SECRET=$$(openssl rand -hex 32)"; \
			echo "POSTGRES_PASSWORD=$$(openssl rand -hex 16)"; \
			echo "PGADMIN_PASSWORD=$$(openssl rand -hex 16)"; \
			echo "HF_TOKEN="; \
		} > .env.$(1).local; \
	}
	@echo ".env.$(1).local ready (secret overrides only)."
endef

env/prod:
	$(call generate-env-overrides,prod,Prod)

env/preprod:
	$(call generate-env-overrides,preprod,Preprod)

# Abort when a non-dev environment is missing secrets or still uses placeholders
define check-secrets
	@if [ ! -s ".env.$(1).local" ]; then \
		echo "ERROR: .env.$(1).local is missing or empty. Run 'make env/$(1)'."; \
		exit 1; \
	fi
	@for key in APP_SECRET POSTGRES_PASSWORD PGADMIN_PASSWORD; do \
		if ! grep -q "^$$key=" ".env.$(1).local" || grep -q "^$$key=change-me$$" ".env.$(1).local"; then \
			echo "ERROR: $$key is missing or still a placeholder in .env.$(1).local. Run 'make env/$(1)' or fill in real values."; \
			exit 1; \
		fi; \
	done
endef

# === ENVIRONMENTS ===

%/up: FORCE
	$(if $(filter $*,$(ENVS)),,$(error Unknown environment "$*". Valid environments: $(ENVS)))
	$(if $(filter $*,$(PULL_ENVS)),$(call check-secrets,$*))
	$(if $(filter $*,$(PULL_ENVS)),$($*_DC) pull)
	$($*_DC) up -d

%/build: FORCE
	$(if $(filter $*,$(ENVS)),,$(error Unknown environment "$*". Valid environments: $(ENVS)))
	$(if $(filter $*,$(PULL_ENVS)),$(call check-secrets,$*))
	$(if $(filter $*,$(PULL_ENVS)),$($*_DC) pull)
	$($*_DC) up --build -d

define service-build-rule
$(1)/build/%: FORCE
	$$($(1)_DC) up --build --no-deps -d $$*
endef
$(foreach env,$(ENVS),$(eval $(call service-build-rule,$(env))))

%/pull: FORCE
	$(if $(filter $*,$(ENVS)),,$(error Unknown environment "$*". Valid environments: $(ENVS)))
	$(if $(filter $*,$(PULL_ENVS)),$(call check-secrets,$*))
	$($*_DC) pull

# === LINTING ===
lint/frontend:
	$(DC_DEV) run --rm --no-deps frontend sh -c "corepack pnpm install && corepack pnpm run lint && corepack pnpm exec tsc --noEmit"

lint/backend:
	$(DC_CI) run --rm --no-deps php sh -c "composer install --no-interaction --prefer-dist && vendor/bin/phpstan analyse && vendor/bin/php-cs-fixer fix --dry-run --diff"

lint/ml:
	$(DC_DEV) run --rm --no-deps ml sh -c "uv run ruff check . && uv run mypy ."

lint: lint/frontend lint/backend lint/ml

# === SECURITY ===
sec/frontend:
	$(DC_DEV) run --rm --no-deps frontend sh -c "corepack pnpm install && corepack pnpm audit"

sec/backend:
	$(DC_CI) run --rm --no-deps php sh -c "composer install --no-interaction --prefer-dist && composer audit"

sec/ml:
	$(DC_DEV) run --rm --no-deps ml sh -c "uv run --with pip-audit pip-audit"

sec: sec/frontend sec/backend sec/ml

# === TEST ===
test/backend:
	$(DC_CI) up -d database redis
	$(DC_CI) run --rm php sh -c "composer install --no-interaction --prefer-dist && php bin/phpunit"

test/frontend:
	$(DC_DEV) run --rm --no-deps frontend sh -c "corepack pnpm install && corepack pnpm test"

test/ml:
	$(DC_DEV) run --rm --no-deps ml sh -c "uv run --with pytest pytest"

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
ci: lint sec test/backend test/frontend test/ml

# === RELEASES (gitflow) ===
release/preprod:
	gh pr create --base preprod --head develop --title "release: develop -> preprod" --fill

release/prod:
	gh pr create --base prod --head preprod --title "release: preprod -> prod" --fill

prune:
	git fetch --prune
	git branch --format '%(refname:short) %(upstream:track)' | awk '$$2 == "[gone]" {print $$1}' | xargs -r git branch -d

# === HELP ===
help:
	@echo " Services : $(SERVICES)"
	@echo " Environments : $(ENVS)"
	@echo ""
	@echo "----- ENVIRONMENTS -----------------------"
	@echo "  env                   -> Generate gitignored local env overrides"
	@echo "  {env}/up              -> Start an environment"
	@echo "  {env}/build           -> Build + start"
	@echo "  {env}/build/{service} -> Rebuild/restart one service"
	@echo "  {env}/pull            -> Pull GHCR images for that environment"
	@echo ""
	@echo "----- LINTING ---------------------------"
	@echo "  lint           -> Run all linters"
	@echo "  lint/{service} -> Run linter for one service"
	@echo ""
	@echo "----- SECURITY ---------------------------"
	@echo "  sec           -> Run all security checks"
	@echo "  sec/{service} -> Run security check for one service"
	@echo ""
	@echo "----- TEST -------------------------------"
	@echo "  test           -> Run all tests"
	@echo "  test/{service} -> Run test for one service"
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
