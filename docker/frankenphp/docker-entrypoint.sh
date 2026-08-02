#!/bin/sh
set -eu

# Entrypoint for the FrankenPHP API container.
. /usr/local/lib/docker-entrypoint-share.sh

exec "$@"
