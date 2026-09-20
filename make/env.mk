.PHONY: env env/prod env/preprod terminal

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

# Display URLs for the given environment
define display-urls
	@echo ""; \
	echo "✓ Environment $(1) is running"; \
	echo ""; \
	if [ "$(1)" = "dev" ]; then \
		domain=$$(grep '^PROJECT_DOMAIN=' .env | cut -d= -f2); \
		echo "Services:"; \
		echo "  Frontend:      http://$$domain"; \
		echo "  API:           http://$$domain/api"; \
		echo "  ML:            http://$$domain/ml"; \
		echo "  PgAdmin:       http://localhost:5050"; \
		echo "  RedisInsight:  http://localhost:5540"; \
		echo "  Mailpit:       http://localhost:8025"; \
		echo "  Postgres:      postgresql://localhost:5432"; \
		echo "  Redis cache:   redis://localhost:6379"; \
		echo "  Redis session: redis://localhost:6380"; \
	elif [ "$(1)" = "preprod" ] || [ "$(1)" = "prod" ]; then \
		domain=$$(grep '^PROJECT_DOMAIN=' .env.$(1) | cut -d= -f2); \
		echo "Services:"; \
		echo "  Frontend:      https://$$domain"; \
		echo "  API:           https://$$domain/api"; \
		echo "  ML:            https://$$domain/ml"; \
	fi; \
	echo ""
endef

# === ENVIRONMENTS ===

%/up: FORCE
	$(if $(filter $*,$(ENVS)),,$(error Unknown environment "$*". Valid environments: $(ENVS)))
	$(if $(filter $*,$(PULL_ENVS)),$(call check-secrets,$*))
	$(if $(filter $*,$(PULL_ENVS)),$($*_DC) pull)
	$($*_DC) up -d
	$(call display-urls,$*)

%/build: FORCE
	$(if $(filter $*,$(ENVS)),,$(error Unknown environment "$*". Valid environments: $(ENVS)))
	$(if $(filter $*,$(PULL_ENVS)),$(call check-secrets,$*))
	$(if $(filter $*,$(PULL_ENVS)),$($*_DC) pull)
	$($*_DC) up --build -d
	$(call display-urls,$*)

define service-build-rule
$(1)/build/%: FORCE
	$$($(1)_DC) up --build --no-deps -d $$*
endef
$(foreach env,$(ENVS),$(eval $(call service-build-rule,$(env))))

%/pull: FORCE
	$(if $(filter $*,$(ENVS)),,$(error Unknown environment "$*". Valid environments: $(ENVS)))
	$(if $(filter $*,$(PULL_ENVS)),$(call check-secrets,$*))
	$($*_DC) pull

%/stop: FORCE
	$(if $(filter $*,$(ENVS)),,$(error Unknown environment "$*". Valid environments: $(ENVS)))
	$($*_DC) stop

terminal:
	$(DC_DEV) run --rm api bash
