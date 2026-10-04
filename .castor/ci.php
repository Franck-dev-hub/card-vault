<?php

declare(strict_types=1);

namespace ci;

use Castor\Attribute\AsTask;
use Symfony\Component\Dotenv\Dotenv;

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
    $buildArgs = build_args();

    foreach (IMAGES as $name => [$file, $target]) {
        io()->section('Docker build (' . $name . ')');
        run(\sprintf(
            'git archive %s | docker build -f %s%s%s -t card-vault/%s:local -',
            $tree,
            $file,
            $buildArgs,
            null === $target ? '' : ' --target ' . $target,
            $name,
        ));
    }

    io()->success('Docker images built.');
}

function build_args(): string
{
    $env = (new Dotenv())->parse((string) file_get_contents(\dirname(__DIR__) . '/.env'));
    $args = '';
    foreach ($env as $name => $value) {
        if (1 === preg_match('/_(VERSION|DIGEST)$/', $name)) {
            $args .= ' --build-arg ' . escapeshellarg($name . '=' . $value);
        }
    }

    return $args;
}

#[AsTask(name: 'all', description: 'Run every CI check (no E2E), Docker builds included', aliases: ['ci'])]
function all(): void
{
    // Fail on a version drift before the long checks and builds.
    \lint\versions();
    frontend();
    ml();
    backend();
    docker();
}
