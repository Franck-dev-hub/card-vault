<?php

declare(strict_types=1);

namespace ci;

use Castor\Attribute\AsTask;

use function Castor\io;

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

#[AsTask(name: 'all', description: 'Run every CI check (no E2E)', aliases: ['ci'])]
function all(): void
{
    frontend();
    ml();
    backend();
}
