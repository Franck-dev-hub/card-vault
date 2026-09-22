<?php

declare(strict_types=1);

namespace frontend;

use Castor\Attribute\AsRawTokens;
use Castor\Attribute\AsTask;

#[AsTask(name: 'pnpm', description: 'Run pnpm in the frontend, e.g. "castor frontend:pnpm add foo"')]
function pnpm(#[AsRawTokens] array $arguments): void
{
    \exec_in(\App::Frontend, ['corepack', 'pnpm', ...$arguments]);
}
