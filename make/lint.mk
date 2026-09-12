.PHONY: lint lint/frontend lint/backend lint/ml lint/doctrine
.PHONY: lint/fix lint/frontend/fix lint/backend/fix lint/ml/fix

# === LINTERS ===
lint/frontend:
	$(RUN_FRONTEND) sh -c "corepack pnpm install && corepack pnpm run lint && corepack pnpm exec tsc --noEmit"

lint/backend:
	$(DC_CI) run --rm --no-deps php sh -c "composer install --no-interaction --prefer-dist && composer validate && vendor/bin/phpstan analyse && vendor/bin/php-cs-fixer fix --dry-run --diff && vendor/bin/rector process --dry-run && bin/console lint:container -e prod"

lint/ml:
	$(DC_DEV) run --rm --no-deps ml sh -c "uv run ruff check . && uv run mypy ."

lint/doctrine:
	$(DC_CI) up -d database
	$(DC_CI) run --rm php sh -c "composer install --no-interaction --prefer-dist && bin/console doctrine:schema:validate --skip-sync"

lint: lint/frontend lint/backend lint/ml lint/doctrine

# === LINT FIX ===
lint/frontend/fix:
	$(RUN_FRONTEND) sh -c "corepack pnpm install && corepack pnpm run lint --fix"

lint/backend/fix:
	$(DC_CI) run --rm --no-deps php sh -c "composer install --no-interaction --prefer-dist && vendor/bin/php-cs-fixer fix && vendor/bin/rector process"

lint/ml/fix:
	$(DC_DEV) run --rm --no-deps ml sh -c "uv run ruff check --fix ."

lint/fix: lint/frontend/fix lint/backend/fix lint/ml/fix
