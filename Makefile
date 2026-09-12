.DEFAULT_GOAL := help
SHELL := /bin/bash
.SHELLFLAGS := -eu -o pipefail -c

# Variables
# Base .env first, then per-env overrides, gitignored local secrets last
DC_DEV = docker compose -f docker/compose.yaml -f docker/compose.dev.yaml --env-file .env --env-file .env.local
DC_PROD = docker compose -f docker/compose.yaml -f docker/compose.prod.yaml --env-file .env --env-file .env.prod --env-file .env.prod.local
DC_PREPROD = docker compose -f docker/compose.yaml -f docker/compose.preprod.yaml --env-file .env --env-file .env.preprod --env-file .env.preprod.local
DC_CI = docker compose -f docker/compose.yaml -f docker/compose.ci.yaml --env-file .env --env-file .env.local
RUN_FRONTEND = $(DC_DEV) run --rm --no-deps -e COREPACK_ENABLE_DOWNLOAD_PROMPT=0 frontend

# Environments and which ones pull GHCR images before starting
ENVS := dev preprod prod
PULL_ENVS := preprod prod
SERVICES := frontend backend ml

dev_DC = $(DC_DEV)
preprod_DC = $(DC_PREPROD)
prod_DC = $(DC_PROD)

.PHONY: help ci FORCE

FORCE:

include $(wildcard make/*.mk)

# === CI ===
ci: lint sec test/backend test/frontend test/ml test/infection

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
	@echo "  {env}/stop            -> Stop an environment"
	@echo "  terminal              -> Open a shell in the backend PHP container"
	@echo ""
	@echo "----- LINTING ---------------------------"
	@echo "  lint               -> Run all linters"
	@echo "  lint/{service}     -> Run linter for one service"
	@echo "  lint/doctrine      -> Validate Doctrine schema"
	@echo "  lint/fix           -> Auto-fix all services"
	@echo "  lint/{service}/fix -> Auto-fix one service"
	@echo ""
	@echo "----- SECURITY ---------------------------"
	@echo "  sec           -> Run all security checks"
	@echo "  sec/{service} -> Run security check for one service"
	@echo ""
	@echo "----- TEST -------------------------------"
	@echo "  test           -> Run all tests"
	@echo "  test/{service} -> Run test for one service"
	@echo "  test/e2e       -> Playwright (apps/frontend)"
	@echo "  test/infection -> Mutation testing"
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
	@echo ""
