<?php

declare(strict_types=1);

use Castor\Context;
use Symfony\Component\Process\Process;

use function Castor\context;
use function Castor\run;

function runtime(): Runtime
{
    return Runtime::from(context()['RUNTIME']);
}

function docker_compose(array $arguments, ?Context $context = null, bool $ci = false): Process
{
    // Checked here: castor 1.7 evaluates `enabled` before the context exists.
    if (Runtime::Host !== runtime()) {
        throw new RuntimeException('This task drives the Docker stack: run it on the host, or set CASTOR_RUNTIME=host.');
    }

    $root = dirname(__DIR__);

    if (!is_file($root . '/.env.local')) {
        throw new RuntimeException('.env.local is missing. Run "castor setup:env" first.');
    }

    return run([
        'docker', 'compose',
        '-f', 'docker/compose.yaml',
        '-f', $ci ? 'docker/compose.ci.yaml' : 'docker/compose.dev.yaml',
        '--env-file', '.env',
        '--env-file', '.env.local',
        ...$arguments,
    ], context: ($context ?? context())->withWorkingDirectory($root));
}

function exec_in(App $app, array $command, array $environment = [], ?Context $context = null): Process
{
    $context ??= context();
    // corepack otherwise hangs on a download prompt.
    $environment += ['COREPACK_ENABLE_DOWNLOAD_PROMPT' => '0'];

    if (Runtime::Ci === runtime()) {
        return run($command, context: $context
            ->withWorkingDirectory($app->directory())
            ->withEnvironment($environment));
    }

    $envFlags = [];
    foreach ($environment as $name => $value) {
        array_push($envFlags, '-e', $name . '=' . $value);
    }

    return docker_compose(['exec', ...$envFlags, $app->value, ...$command], $context);
}

function run_in_playwright(array $command): Process
{
    return docker_compose([
        '--profile', 'e2e',
        'run', '--rm', '--no-deps', '-T',
        '-e', 'COREPACK_ENABLE_DOWNLOAD_PROMPT=0',
        'playwright',
        ...$command,
    ], ci: true);
}
