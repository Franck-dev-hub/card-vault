#!/bin/sh
set -eu

# Entrypoint for the one-shot database migration runner.
. /usr/local/lib/docker-entrypoint-share.sh

exec "$@"
