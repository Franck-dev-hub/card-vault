<?php

declare(strict_types=1);

namespace setup;

use Castor\Attribute\AsTask;

use function Castor\context;
use function Castor\fs;
use function Castor\io;
use function Castor\run;

#[AsTask(
    name: 'env',
    description: 'Generate the gitignored .env.local with random dev secrets and enable the git hooks',
)]
function env(): void
{
    $root = \dirname(__DIR__);

    run(['git', 'config', 'core.hooksPath', '.githooks']);

    if (is_file($root . '/.env.local')) {
        io()->note('.env.local already exists, left untouched.');

        return;
    }

    // One password to remember in dev.
    $password = bin2hex(random_bytes(16));
    $appSecret = bin2hex(random_bytes(32));

    // Secrets only: a full copy would pin every version bumped in .env later.
    fs()->dumpFile($root . '/.env.local', <<<ENV
        # API / Symfony
        APP_SECRET={$appSecret}

        # Postgres
        POSTGRES_PASSWORD={$password}

        # Postgres admin
        PGADMIN_PASSWORD={$password}

        # ML service
        HF_TOKEN=change-me

        ENV);

    io()->success('.env.local ready (dev secrets). Set HF_TOKEN before the first ML start.');
}

#[AsTask(name: 'backend', description: 'Install PHP dependencies')]
function backend(): void
{
    \exec_in(\App::Backend, ['composer', 'install', '--no-interaction', '--prefer-dist']);
}

#[AsTask(name: 'frontend', description: 'Install JS dependencies')]
function frontend(): void
{
    \exec_in(\App::Frontend, ['pnpm', 'install']);
}

#[AsTask(name: 'ml', description: 'Install Python dependencies')]
function ml(): void
{
    // On the host, dependencies live in the root-owned image venv.
    if (\Runtime::Host === \runtime()) {
        \docker_compose(['up', '--build', '--no-deps', '-d', 'ml']);

        return;
    }

    run(['uv', 'sync', '--frozen'], context: context()->withWorkingDirectory(\App::Ml->directory()));
}
