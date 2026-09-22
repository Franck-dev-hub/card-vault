<?php

declare(strict_types=1);

namespace backend;

use Castor\Attribute\AsRawTokens;
use Castor\Attribute\AsTask;

#[AsTask(name: 'migrate', description: 'Apply pending Doctrine migrations')]
function migrate(): void
{
    \exec_in(\App::Backend, ['bin/console', 'doctrine:migrations:migrate', '--no-interaction', '--all-or-nothing', '--allow-no-migration']);
}

#[AsTask(name: 'migrate-diff', description: 'Generate a migration from entity changes')]
function migrate_diff(): void
{
    \exec_in(\App::Backend, ['bin/console', 'doctrine:migrations:diff']);
}

#[AsTask(name: 'composer', description: 'Run Composer in the backend, e.g. "castor backend:composer require foo/bar"')]
function composer(#[AsRawTokens] array $arguments): void
{
    \exec_in(\App::Backend, ['composer', ...$arguments]);
}
