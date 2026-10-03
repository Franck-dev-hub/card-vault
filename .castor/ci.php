<?php

declare(strict_types=1);

namespace ci;

use Castor\Attribute\AsTask;

use function Castor\capture;
use function Castor\io;
use function Castor\run;

#[AsTask(name: 'backend', description: 'Run the backend CI checks: lint, security, PHPUnit, Infection')]
function backend(): void
{
    \lint\backend();
    \security\backend();
    \tests\backend();
    \tests\infection();

    io()->success('Backend CI checks passed.');
}

#[AsTask(name: 'frontend', description: 'Run the frontend CI checks: lint, security, Vitest')]
function frontend(): void
{
    \lint\frontend();
    \security\frontend();
    \tests\frontend();

    io()->success('Frontend CI checks passed.');
}

#[AsTask(name: 'ml', description: 'Run the ML CI checks: lint, security, pytest')]
function ml(): void
{
    \lint\ml();
    \security\ml();
    \tests\ml();

    io()->success('ML CI checks passed.');
}

const IMAGES = [
    'api' => ['docker/Dockerfile', 'frankenphp_release'],
    'frontend' => ['docker/frontend/Dockerfile', null],
    'ml' => ['docker/ml/Dockerfile', 'ml_release'],
];

#[AsTask(name: 'docker', description: 'Build the release images from the git tree, as the CI does')]
function docker(): void
{
    if (\Runtime::Host !== \runtime()) {
        throw new \RuntimeException('This task builds Docker images: run it on the host.');
    }

    // The CI checkout has no gitignored files; a local context can hide a broken build.
    $tree = trim(capture(['git', 'stash', 'create'])) ?: 'HEAD';

    foreach (IMAGES as $name => [$file, $target]) {
        io()->section('Docker build (' . $name . ')');
        run(\sprintf(
            'git archive %s | docker build -f %s%s -t card-vault/%s:local -',
            $tree,
            $file,
            null === $target ? '' : ' --target ' . $target,
            $name,
        ));
    }

    io()->success('Docker images built.');
}

#[AsTask(name: 'all', description: 'Run every CI check (no E2E), Docker builds included', aliases: ['ci'])]
function all(): void
{
    frontend();
    ml();
    backend();
    docker();
}
