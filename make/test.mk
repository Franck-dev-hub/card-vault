.PHONY: test test/backend test/frontend test/ml test/e2e test/infection

# === TESTS ===
test/backend:
	$(DC_CI) up -d database
	$(DC_CI) run --rm php sh -c "composer install --no-interaction --prefer-dist && php bin/phpunit"

test/frontend:
	$(RUN_FRONTEND) sh -c "corepack pnpm install && corepack pnpm test"

test/ml:
	$(DC_DEV) run --rm --no-deps ml sh -c "uv run --with pytest pytest"

test/e2e:
	$(DC_DEV) up -d
	$(DC_CI) --profile e2e run --rm --no-deps -e COREPACK_ENABLE_DOWNLOAD_PROMPT=0 playwright sh -c "corepack pnpm install && corepack pnpm run test:e2e"

test/infection:
	$(DC_CI) up -d database
	$(DC_CI) run --rm php sh -c "composer install --no-interaction --prefer-dist && vendor/bin/infection"

test: test/backend test/frontend test/ml test/e2e test/infection
