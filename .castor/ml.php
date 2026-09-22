<?php

declare(strict_types=1);

namespace ml;

use Castor\Attribute\AsRawTokens;
use Castor\Attribute\AsTask;

#[AsTask(name: 'uv', description: 'Run uv in the ML service, e.g. "castor ml:uv add foo"')]
function uv(#[AsRawTokens] array $arguments): void
{
    \exec_in(\App::Ml, ['uv', ...$arguments]);
}
