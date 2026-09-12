.PHONY: migrate migrate-diff

# === MIGRATIONS ===
migrate:
	$(DC_CI) run --rm --no-deps -e APP_ENV=dev php sh -c "composer install --no-interaction --prefer-dist && php bin/console doctrine:migrations:migrate --no-interaction --all-or-nothing --allow-no-migration"

migrate-diff:
	$(DC_CI) run --rm --no-deps -e APP_ENV=dev php sh -c "composer install --no-interaction --prefer-dist && php bin/console doctrine:migrations:diff"
