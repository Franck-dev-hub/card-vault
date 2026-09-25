<?php

declare(strict_types=1);

namespace tests;

use Castor\Attribute\AsOption;
use Castor\Attribute\AsTask;

use function Castor\io;

#[AsTask(name: 'phpunit', namespace: 'tests:backend', description: 'Run PHPUnit', aliases: ['phpunit'])]
function phpunit(): void
{
    io()->section('PHPUnit');
    // TestDox for humans, compact dots for CI logs.
    $options = \Runtime::Ci === \runtime() ? [] : ['--testdox'];
    \exec_in(\App::Backend, ['php', 'bin/phpunit', ...$options]);
}

#[AsTask(
    name: 'infection',
    namespace: 'tests:backend',
    description: 'Run mutation testing (Infection)',
    aliases: ['infection'],
)]
function infection(
    #[AsOption(description: 'CI only: mutate lines changed since this git ref, e.g. origin/develop')]
    ?string $diffBase = null,
): void {
    // The dev container has no .git.
    if (null !== $diffBase && \Runtime::Ci !== \runtime()) {
        throw new \RuntimeException('--diff-base needs the git repository, available in CI only.');
    }

    io()->section('Infection');
    // A diff without mutable lines would otherwise score 0% and fail.
    $diffOptions = null !== $diffBase
        ? ['--git-diff-lines', '--git-diff-base=' . $diffBase, '--ignore-msi-with-no-mutations']
        : [];

    \exec_in(\App::Backend, ['vendor/bin/infection', '--threads=max', '--show-mutations', ...$diffOptions]);
}

#[AsTask(name: 'backend', description: 'Run the backend tests (PHPUnit)')]
function backend(): void
{
    phpunit();
}

#[AsTask(name: 'vitest', namespace: 'tests:frontend', description: 'Run the frontend unit tests (Vitest)')]
function vitest(): void
{
    io()->section('Vitest');
    \exec_in(\App::Frontend, ['corepack', 'pnpm', 'test']);
}

#[AsTask(name: 'frontend', description: 'Run the frontend tests (Vitest)')]
function frontend(): void
{
    vitest();
}

#[AsTask(name: 'pytest', namespace: 'tests:ml', description: 'Run the ML tests (pytest)')]
function pytest(): void
{
    io()->section('pytest');
    \exec_in(\App::Ml, ['uv', 'run', '--with', 'pytest', 'pytest']);
}

#[AsTask(name: 'ml', description: 'Run the ML tests (pytest)')]
function ml(): void
{
    pytest();
}

// Needs the whole stack, hence CASTOR_RUNTIME=host in the CI job.
#[AsTask(name: 'e2e', description: 'Run the Playwright E2E suite against the dev stack')]
function e2e(): void
{
    io()->section('Playwright');
    \docker_compose(['up', '-d']);
    \run_in_playwright(['sh', '-c', 'corepack pnpm install && corepack pnpm run test:e2e']);
}

#[AsTask(name: 'all', description: 'Run every test suite, E2E and mutation testing included')]
function all(): void
{
    backend();
    frontend();
    ml();
    e2e();
    infection();
}
