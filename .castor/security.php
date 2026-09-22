<?php

declare(strict_types=1);

namespace security;

use Castor\Attribute\AsTask;

use function Castor\io;

#[AsTask(name: 'backend', description: 'Audit PHP dependencies')]
function backend(): void
{
    io()->section('composer audit');
    \exec_in(\App::Backend, ['composer', 'audit']);
}

#[AsTask(name: 'frontend', description: 'Audit JS dependencies')]
function frontend(): void
{
    io()->section('pnpm audit');
    \exec_in(\App::Frontend, ['corepack', 'pnpm', 'audit']);
}

#[AsTask(name: 'ml', description: 'Audit Python dependencies')]
function ml(): void
{
    io()->section('pip-audit');
    \exec_in(\App::Ml, ['uv', 'run', '--with', 'pip-audit', 'pip-audit']);
}

#[AsTask(name: 'all', description: 'Audit every dependency set')]
function all(): void
{
    backend();
    frontend();
    ml();
}
