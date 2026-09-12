.PHONY: sec sec/frontend sec/backend sec/ml

# === SECURITY ===
sec/frontend:
	$(RUN_FRONTEND) sh -c "corepack pnpm install && corepack pnpm audit"

sec/backend:
	$(DC_CI) run --rm --no-deps php sh -c "composer install --no-interaction --prefer-dist && composer audit"

sec/ml:
	$(DC_DEV) run --rm --no-deps ml sh -c "uv run --with pip-audit pip-audit"

sec: sec/frontend sec/backend sec/ml
