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
    $secrets = [
        'APP_SECRET' => bin2hex(random_bytes(32)),
        'POSTGRES_PASSWORD' => $password,
        'PGADMIN_PASSWORD' => $password,
    ];

    $content = (string) file_get_contents($root . '/.env');
    foreach ($secrets as $name => $value) {
        $content = (string) preg_replace('/^' . $name . '=.*$/m', $name . '=' . $value, $content);
    }

    fs()->dumpFile($root . '/.env.local', $content);

    io()->success('.env.local ready (dev, full copy with generated secrets).');
}

#[AsTask(name: 'backend', description: 'Install PHP dependencies')]
function backend(): void
{
    \exec_in(\App::Backend, ['composer', 'install', '--no-interaction', '--prefer-dist']);
}

#[AsTask(name: 'frontend', description: 'Install JS dependencies')]
function frontend(): void
{
    \exec_in(\App::Frontend, ['corepack', 'pnpm', 'install']);
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
