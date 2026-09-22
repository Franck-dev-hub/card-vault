<?php

declare(strict_types=1);

use Castor\Attribute\AsContext;
use Castor\Context;

use function Castor\guard_min_version;
use function Castor\import;

guard_min_version('v1.7.0');

import(__DIR__ . '/.castor/');

#[AsContext(default: true, name: 'default')]
function default_context(): Context
{
    return new Context(
        data: ['RUNTIME' => detect_runtime()->value],
        workingDirectory: __DIR__,
    );
}

function detect_runtime(): Runtime
{
    // Lets the E2E job drive Docker from the CI runner.
    $forced = getenv('CASTOR_RUNTIME');
    if (false !== $forced && '' !== $forced) {
        return Runtime::tryFrom($forced)
            ?? throw new InvalidArgumentException(\sprintf('CASTOR_RUNTIME must be one of: %s.', implode(', ', array_column(Runtime::cases(), 'value'))));
    }

    return false !== getenv('CI') ? Runtime::Ci : Runtime::Host;
}
