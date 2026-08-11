ARG PHP_VERSION=8.4
ARG FRANKENPHP_VERSION=1.12.6
ARG COMPOSER_VERSION=2.9
ARG NODEJS_VERSION=22
ARG PHP_EXTENSION_INSTALLER_VERSION=2

FROM composer/composer:${COMPOSER_VERSION} AS composer

FROM mlocati/php-extension-installer:${PHP_EXTENSION_INSTALLER_VERSION} AS php_extension_installer

FROM node:${NODEJS_VERSION}-slim AS node

# ---------------------------------------------------------------------------
# FrankenPHP base (web server with embedded Caddy for the Symfony/API Platform app)
# ---------------------------------------------------------------------------
FROM dunglas/frankenphp:${FRANKENPHP_VERSION}-php${PHP_VERSION} AS frankenphp_base

ARG PHP_EXTENSIONS
ARG SESSION_LIFETIME=604800
ARG USER
ARG USER_ID
ARG GROUP_ID

RUN install-php-extensions ${PHP_EXTENSIONS}

RUN addgroup --gid ${GROUP_ID} ${USER} \
    && adduser --system --uid $USER_ID --home /home/$USER --shell /bin/bash $USER \
    && usermod -g $USER $USER

COPY --from=composer /usr/bin/composer /usr/bin/composer

COPY --link docker/php/php.ini $PHP_INI_DIR/conf.d/default.ini

# Bake the session lifetime, let the non-root user bind privileged ports (80/443), own the caddy dirs
RUN sed -i "s/__SESSION_LIFETIME__/${SESSION_LIFETIME}/" "$PHP_INI_DIR/conf.d/default.ini" \
    && setcap CAP_NET_BIND_SERVICE=+eip /usr/local/bin/frankenphp \
    && chown -R ${USER_ID}:${GROUP_ID} /data/caddy /config/caddy

HEALTHCHECK --start-period=15s CMD curl -f http://localhost:2019/metrics || exit 1

# ---------------------------------------------------------------------------
# PHP CLI base (CI, database migration runner)
# ---------------------------------------------------------------------------
FROM php:${PHP_VERSION}-cli AS php_base

ARG PHP_EXTENSIONS
ARG SESSION_LIFETIME=604800
ARG USER
ARG USER_ID
ARG GROUP_ID

COPY --from=php_extension_installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions ${PHP_EXTENSIONS}

RUN addgroup --gid ${GROUP_ID} ${USER} \
    && adduser --system --uid $USER_ID --home /home/$USER --shell /bin/bash $USER \
    && usermod -g $USER $USER

COPY --from=composer /usr/bin/composer /usr/bin/composer

COPY --link docker/php/php.ini $PHP_INI_DIR/conf.d/default.ini
RUN sed -i "s/__SESSION_LIFETIME__/${SESSION_LIFETIME}/" "$PHP_INI_DIR/conf.d/default.ini"

# ---------------------------------------------------------------------------
# Dev image: FrankenPHP + xdebug + node (frontend assets tooling)
# ---------------------------------------------------------------------------
FROM frankenphp_base AS frankenphp_dev

ARG USER
ARG USER_ID
ARG GROUP_ID

ENV APP_ENV=dev
ENV APP_DEBUG=1
ENV COMPOSER_HOME=/home/${USER}/.composer

COPY --from=node /usr/local/lib/node_modules /usr/local/lib/node_modules
COPY --from=node /usr/local/bin /usr/local/bin

# git for patches applier, dnsutils for network debugging
# hadolint ignore=DL3008
RUN <<-EOF
    apt-get update
    apt-get install -y --no-install-recommends git dnsutils
    rm -rf /var/lib/apt/lists/*
    install-php-extensions xdebug pcov
EOF

COPY --link docker/php/pcov.ini $PHP_INI_DIR/conf.d/pcov.ini

# Enable corepack (pnpm auto-provisioned via packageManager field in package.json)
RUN rm -rf /usr/local/bin/yarn /usr/local/bin/yarnpkg && corepack enable

RUN mkdir -p /home/${USER}/.composer && chown ${USER_ID}:${GROUP_ID} /home/${USER}/.composer

RUN echo 'alias ll="ls -alFh"' >> /home/${USER}/.bashrc \
      && echo 'alias sf="bin/console"' >> /home/${USER}/.bashrc

COPY --link docker/php/php.dev.ini $PHP_INI_DIR/conf.d/dev.ini

USER ${USER}
WORKDIR /home/${USER}/sources/api

# ---------------------------------------------------------------------------
# CI image: PHP CLI + pcov for PHPUnit coverage
# ---------------------------------------------------------------------------
FROM php_base AS php_ci

ARG USER
ARG USER_ID
ARG GROUP_ID

# git for patches applier
# hadolint ignore=DL3008
RUN <<-EOF
    apt-get update
    apt-get install -y --no-install-recommends git unzip
    rm -rf /var/lib/apt/lists/*
    install-php-extensions pcov
EOF

COPY --link docker/php/pcov.ini $PHP_INI_DIR/conf.d/pcov.ini

RUN mkdir -p /home/${USER}/.composer && chown ${USER_ID}:${GROUP_ID} /home/${USER}/.composer

USER ${USER}
WORKDIR /home/${USER}/sources/api

# ---------------------------------------------------------------------------
# App builder: installs Composer deps (no-dev)
# Lock-file-first pattern: deps layers are cached when locks are unchanged.
# ---------------------------------------------------------------------------
FROM php_base AS app_builder

ARG USER
ARG USER_ID
ARG GROUP_ID
ARG RELEASE_APP_ENV=prod

ENV APP_ENV=$RELEASE_APP_ENV
ENV APP_DEBUG=0

# Build-only dependencies: git (for composer patches) and unzip
# hadolint ignore=DL3008
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && rm -rf /var/lib/apt/lists/*

USER ${USER}
WORKDIR /home/${USER}/sources/api

# 1. Composer: copy lock files first, install deps (cached if unchanged)
COPY apps/api/composer.json apps/api/composer.lock* ./
RUN composer install --no-ansi --no-progress --no-interaction --no-scripts --no-dev --no-autoloader

# 2. Copy full source, finalize autoloader
COPY apps/api ./

RUN <<-EOF
    set -eux
    composer check-platform-reqs
    composer dump-autoload --optimize --apcu --no-interaction --no-dev
EOF

RUN composer run-script --no-interaction post-install-cmd || true

# Strip vendor dev files to reduce image size (only safe patterns)
RUN find vendor -type d -name ".git" -exec rm -rf {} + 2>/dev/null || true \
    && find vendor -type f \( -name "*.md" -o -name "CHANGELOG*" -o -name "README*" -o -name ".gitignore" -o -name ".gitattributes" -o -name "phpunit.xml*" -o -name "phpstan*" -o -name ".php-cs-fixer*" \) -delete 2>/dev/null || true

# ---------------------------------------------------------------------------
# App sources: cherry-pick only runtime files from the builder
# ---------------------------------------------------------------------------
FROM scratch AS app_sources

ARG USER
ARG USER_ID
ARG GROUP_ID
ARG RELEASE_APP_CONFIG_ENV=prod

COPY --from=app_builder --chown=${USER_ID}:${GROUP_ID} /home/${USER}/sources/api/bin /sources/bin
COPY --from=app_builder --chown=${USER_ID}:${GROUP_ID} /home/${USER}/sources/api/config /sources/config
COPY --from=app_builder --chown=${USER_ID}:${GROUP_ID} /home/${USER}/sources/api/migrations /sources/migrations
COPY --from=app_builder --chown=${USER_ID}:${GROUP_ID} /home/${USER}/sources/api/public /sources/public
COPY --from=app_builder --chown=${USER_ID}:${GROUP_ID} /home/${USER}/sources/api/src /sources/src
COPY --from=app_builder --chown=${USER_ID}:${GROUP_ID} /home/${USER}/sources/api/templates /sources/templates
COPY --from=app_builder --chown=${USER_ID}:${GROUP_ID} /home/${USER}/sources/api/vendor /sources/vendor
COPY --from=app_builder --chown=${USER_ID}:${GROUP_ID} /home/${USER}/sources/api/composer.json /sources/composer.json
COPY --from=app_builder --chown=${USER_ID}:${GROUP_ID} /home/${USER}/sources/api/composer.lock /sources/composer.lock

COPY --chmod=755 docker/share/docker-entrypoint-share.sh /share/docker-entrypoint-share.sh

# ---------------------------------------------------------------------------
# FrankenPHP release image (api)
# ---------------------------------------------------------------------------
FROM frankenphp_base AS frankenphp_release

ARG USER
ARG USER_ID
ARG GROUP_ID
ARG RELEASE_VERSION
ARG RELEASE_APP_CONFIG_ENV=prod

ENV APP_CONFIG_ENV=$RELEASE_APP_CONFIG_ENV
ENV APP_DEBUG=0
ENV CONTAINER_USER=${USER}
ENV CADDY_GLOBAL_LOG_LEVEL=warn
ENV CADDY_SERVER_LOG_LEVEL=warn
ENV CADDY_ROOT_FOLDER=/home/${USER}/sources/api/public

LABEL org.opencontainers.image.title="card-vault api"
LABEL org.opencontainers.image.version=${RELEASE_VERSION}

COPY --from=app_sources --chown=${USER_ID}:${GROUP_ID} /sources /home/${USER}/sources/api
COPY --from=app_sources /share/docker-entrypoint-share.sh /usr/local/lib/docker-entrypoint-share.sh

COPY --link docker/php/php.prod.ini $PHP_INI_DIR/conf.d/prod.ini
RUN sed -i "s/__USER__/${USER}/" "$PHP_INI_DIR/conf.d/prod.ini"
COPY --link docker/caddy/api.Caddyfile /etc/frankenphp/Caddyfile
COPY --link --chmod=755 docker/frankenphp/docker-entrypoint.sh /usr/local/bin/docker-entrypoint

# Security hardening: strip setuid/setgid bits
RUN find / -perm /6000 -type f -exec chmod a-s {} + 2>/dev/null || true

WORKDIR /home/${USER}/sources/api

USER ${USER}

RUN <<-EOF
    set -eux
    APP_ENV=prod php bin/console cache:warmup
    APP_ENV=prod php bin/console lint:container
EOF

ENTRYPOINT ["docker-entrypoint"]

CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]

# ---------------------------------------------------------------------------
# Migration runner (one-shot deploy job)
# ---------------------------------------------------------------------------
FROM php_base AS database_migrate

ARG USER
ARG USER_ID
ARG GROUP_ID
ARG RELEASE_VERSION
ARG RELEASE_APP_CONFIG_ENV=prod

ENV APP_CONFIG_ENV=$RELEASE_APP_CONFIG_ENV
ENV APP_DEBUG=0
ENV CONTAINER_USER=${USER}

LABEL org.opencontainers.image.title="card-vault database-migrate"
LABEL org.opencontainers.image.version=${RELEASE_VERSION}

USER root

COPY --from=app_sources --chown=${USER_ID}:${GROUP_ID} /sources /home/${USER}/sources/api
COPY --from=app_sources /share/docker-entrypoint-share.sh /usr/local/lib/docker-entrypoint-share.sh

COPY --link docker/php/php.prod.ini $PHP_INI_DIR/conf.d/prod.ini
RUN sed -i "s/__USER__/${USER}/" "$PHP_INI_DIR/conf.d/prod.ini"
COPY --link --chmod=755 docker/database-migrate/docker-entrypoint.sh /usr/local/bin/docker-entrypoint

# Security hardening: strip setuid/setgid bits
RUN find / -perm /6000 -type f -exec chmod a-s {} + 2>/dev/null || true

WORKDIR /home/${USER}/sources/api

USER ${USER}

ENTRYPOINT ["docker-entrypoint"]

CMD ["php", "bin/console", "doctrine:migrations:migrate", "--no-interaction", "--all-or-nothing", "--allow-no-migration"]
