#!/usr/bin/env bash
#######################################
# Deploys the prod stack when the api image tagged CARD_VAULT_TAG changes on the
# registry: checks out its git tag, pulls the images, restarts the stack, probes
# /health and reports to Telegram. Run by launchd every 5 minutes, see
# docs/getting-started/deployment.md.
# Globals:
#   CARD_VAULT_DIR  Checkout of this repository, defaults to ~/Server/card-vault.
#   CARD_VAULT_TAG  Image tag to watch, defaults to prod.
#   TELEGRAM_BOT_TOKEN, TELEGRAM_CHAT_ID
#                   Telegram bot and chat; read from DEPLOY_ENV_FILE when unset;
#                   no notification when empty.
#   DEPLOY_ENV_FILE Private env file, defaults to ~/.config/card-vault-deploy/env.
#   DRY_RUN         1 prints the decision and changes nothing.
#   XDG_STATE_HOME  Parent of the state folder, defaults to ~/.local/state.
# Arguments:
#   None
# Outputs:
#   Nothing when the stack is up to date; one line per step during a deploy.
#   Errors on stderr.
# Returns:
#   0 when up to date, deployed or waiting for images; 1 on failure.
#######################################
set -euo pipefail

readonly CARD_VAULT_DIR="${CARD_VAULT_DIR:-$HOME/Server/card-vault}"
readonly CARD_VAULT_TAG="${CARD_VAULT_TAG:-prod}"
readonly DEPLOY_ENV_FILE="${DEPLOY_ENV_FILE:-$HOME/.config/card-vault-deploy/env}"
readonly DRY_RUN="${DRY_RUN:-0}"
readonly STATE_DIR="${XDG_STATE_HOME:-$HOME/.local/state}/card-vault-deploy"
readonly SERVICES="api frontend ml"
readonly HEALTH_TRIES=30
readonly HEALTH_DELAY=10
readonly MANIFEST_TYPES="application/vnd.oci.image.index.v1+json,application/vnd.oci.image.manifest.v1+json,application/vnd.docker.distribution.manifest.list.v2+json,application/vnd.docker.distribution.manifest.v2+json"

log() {
    echo "$(date '+%F %T') $*"
}

err() {
    echo "$(date '+%F %T') ERROR: $*" >&2
}

# Reads one value without sourcing: an env file is not a shell script.
env_value() {
    sed -n -E "s/^$2=\"?([^\"]*)\"?$/\1/p" "$1" | tail -n 1
}

# Returns 1 when the tag does not exist, 2 when the registry fails.
registry_digest() {
    local repository="${REGISTRY_NAMESPACE}/$1" tag=$2 token headers status

    token=$(curl -fsS --max-time 20 \
        "https://${REGISTRY_HOST}/token?scope=repository:${repository}:pull" | jq -r .token) || return 2
    headers=$(curl -sSI --max-time 20 -H "Authorization: Bearer ${token}" -H "Accept: ${MANIFEST_TYPES}" \
        "https://${REGISTRY_HOST}/v2/${repository}/manifests/${tag}" | tr -d '\r') || return 2
    status=$(echo "$headers" | awk 'NR == 1 { print $2 }')

    case "$status" in
        200) echo "$headers" | awk -F': ' 'tolower($1) == "docker-content-digest" { print $2 }' ;;
        404) return 1 ;;
        *) return 2 ;;
    esac
}

# The token goes through stdin: on the command line, ps would show it to every user.
notify() {
    [[ -n "$TELEGRAM_BOT_TOKEN" && -n "$TELEGRAM_CHAT_ID" ]] || return 0
    printf 'url = "https://api.telegram.org/bot%s/sendMessage"\n' "$TELEGRAM_BOT_TOKEN" \
        | curl -fsS --max-time 20 -o /dev/null -K - \
            --data-urlencode "chat_id=${TELEGRAM_CHAT_ID}" --data-urlencode "text=$1" \
        || err "Telegram notification failed"
}

# A killed run leaves its lock behind: take it over once its process is gone.
lock() {
    local pid

    mkdir -p "$STATE_DIR"
    if ! mkdir "$STATE_DIR/lock" 2>/dev/null; then
        pid=$(cat "$STATE_DIR/lock/pid" 2>/dev/null || true)
        if [[ -n "$pid" ]] && kill -0 "$pid" 2>/dev/null; then
            exit 0
        fi
        log "Taking over the stale lock of process ${pid:-unknown}"
    fi
    echo $$ > "$STATE_DIR/lock/pid"
    trap 'rm -rf "$STATE_DIR/lock"' EXIT
}

# A placeholder secret would run prod on a value published in the repository.
check_secrets() {
    local key

    if [[ ! -f .env.prod.local ]]; then
        err ".env.prod.local is missing in ${CARD_VAULT_DIR}"
        return 1
    fi
    for key in APP_SECRET POSTGRES_PASSWORD; do
        if grep -q "^${key}=change-me$" .env.prod.local; then
            err "${key} is still a placeholder in .env.prod.local"
            return 1
        fi
    done
}

compose() {
    docker compose -f docker/compose.yaml -f docker/compose.prod.yaml \
        --env-file .env --env-file .env.prod --env-file .env.prod.local "$@"
}

wait_for_health() {
    local try

    for ((try = 1; try <= HEALTH_TRIES; try++)); do
        if compose exec -T api curl -fsS --max-time 5 http://localhost:8000/health > /dev/null 2>&1; then
            return 0
        fi
        sleep "$HEALTH_DELAY"
    done
    return 1
}

# Runs behind ||, where set -e is off: each step returns 1 itself; 3 = release incomplete.
deploy() {
    local digest=$1 image labels revision version service

    image="${REGISTRY_HOST}/${REGISTRY_NAMESPACE}/api/${PROJECT_ENV}"
    docker pull -q "${image}@${digest}" > /dev/null || return 1
    labels=$(docker image inspect "${image}@${digest}" --format \
        '{{index .Config.Labels "org.opencontainers.image.revision"}} {{index .Config.Labels "org.opencontainers.image.version"}}') \
        || return 1
    read -r revision version <<< "$labels" || true
    if [[ -z "$revision" ]]; then
        err "${image}@${digest} has no revision label"
        return 1
    fi

    # The images are pushed one by one: wait until the whole release is there.
    for service in $SERVICES; do
        registry_digest "${service}/${PROJECT_ENV}" "sha-${revision}" > /dev/null || return 3
    done
    log "New api:${CARD_VAULT_TAG} digest ${digest}"

    git fetch -q --tags --force origin || return 1
    if [[ "$version" == v* ]] && git rev-parse -q --verify "refs/tags/${version}" > /dev/null; then
        DEPLOYED_REF=$version
    else
        DEPLOYED_REF=$revision
    fi
    log "Checking out ${DEPLOYED_REF}"
    git checkout -q "$DEPLOYED_REF" || return 1

    check_secrets || return 1

    # Pinned to the revision, so a push during the deploy cannot mix two releases.
    export PROJECT_IMAGE_TAG="sha-${revision}"
    log "Pulling ${PROJECT_IMAGE_TAG}"
    compose pull -q || return 1
    log "Starting the stack"
    compose up -d --remove-orphans || return 1

    log "Waiting for /health"
    if ! wait_for_health; then
        err "/health did not answer after $((HEALTH_TRIES * HEALTH_DELAY)) seconds"
        return 1
    fi

    docker image prune -f > /dev/null || true
}

main() {
    local digest deployed failed status

    cd "$CARD_VAULT_DIR"
    lock

    REGISTRY_HOST=$(env_value .env DOCKER_REGISTRY)
    REGISTRY_NAMESPACE=${REGISTRY_HOST#*/}
    REGISTRY_HOST=${REGISTRY_HOST%%/*}
    PROJECT_ENV=$(env_value .env.prod PROJECT_ENV)
    TELEGRAM_BOT_TOKEN="${TELEGRAM_BOT_TOKEN:-}"
    TELEGRAM_CHAT_ID="${TELEGRAM_CHAT_ID:-}"
    if [[ -f "$DEPLOY_ENV_FILE" ]]; then
        [[ -n "$TELEGRAM_BOT_TOKEN" ]] || TELEGRAM_BOT_TOKEN=$(env_value "$DEPLOY_ENV_FILE" TELEGRAM_BOT_TOKEN)
        [[ -n "$TELEGRAM_CHAT_ID" ]] || TELEGRAM_CHAT_ID=$(env_value "$DEPLOY_ENV_FILE" TELEGRAM_CHAT_ID)
    fi
    DEPLOYED_REF=""

    status=0
    digest=$(registry_digest "api/${PROJECT_ENV}" "$CARD_VAULT_TAG") || status=$?
    if ((status != 0)); then
        # Logged once per outage, so a long one does not fill the log.
        if [[ ! -f "$STATE_DIR/registry-error" ]]; then
            err "cannot read api/${PROJECT_ENV}:${CARD_VAULT_TAG} from ${REGISTRY_HOST} (code ${status})"
            touch "$STATE_DIR/registry-error"
        fi
        return 1
    fi
    rm -f "$STATE_DIR/registry-error"

    deployed=$(cat "$STATE_DIR/deployed" 2>/dev/null || true)
    failed=$(cat "$STATE_DIR/failed" 2>/dev/null || true)

    if [[ "$DRY_RUN" == 1 ]]; then
        echo "remote:   ${digest}"
        echo "deployed: ${deployed:-none}"
        echo "failed:   ${failed:-none}"
        if [[ "$digest" == "$deployed" || "$digest" == "$failed" ]]; then
            echo "decision: nothing to do"
        else
            echo "decision: deploy"
        fi
        return 0
    fi

    # A failed release is retried only once a new one replaces it.
    [[ "$digest" != "$deployed" && "$digest" != "$failed" ]] || return 0

    status=0
    deploy "$digest" || status=$?

    case "$status" in
        0)
            echo "$digest" > "$STATE_DIR/deployed"
            rm -f "$STATE_DIR/failed"
            log "Deployed ${DEPLOYED_REF}"
            notify "card-vault: ${DEPLOYED_REF} is live."
            ;;
        3)
            if [[ "$(cat "$STATE_DIR/waiting" 2>/dev/null || true)" != "$digest" ]]; then
                log "Waiting for every image of ${digest}"
                echo "$digest" > "$STATE_DIR/waiting"
            fi
            ;;
        *)
            echo "$digest" > "$STATE_DIR/failed"
            # The stack no longer runs the last good digest: promoting it again must redeploy.
            rm -f "$STATE_DIR/deployed"
            err "deploy of ${digest} failed"
            notify "card-vault: deploy of ${DEPLOYED_REF:-$digest} failed, see ~/Library/Logs/card-vault-deploy.log."
            return 1
            ;;
    esac
}

# Kept on one line: the git checkout replaces this file while it runs.
main "$@"; exit
