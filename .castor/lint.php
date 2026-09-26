<?php

declare(strict_types=1);

namespace lint;

use Castor\Attribute\AsOption;
use Castor\Attribute\AsTask;

use function Castor\io;

#[AsTask(name: 'composer', namespace: 'lint:backend', description: 'Validate composer.json and composer.lock')]
function composer(): void
{
    io()->section('composer validate');
    \exec_in(\App::Backend, ['composer', 'validate']);
}

#[AsTask(
    name: 'phpstan',
    namespace: 'lint:backend',
    description: 'Analyse PHP code with PHPStan',
    aliases: ['phpstan'],
)]
function phpstan(): void
{
    io()->section('PHPStan');
    \exec_in(\App::Backend, ['vendor/bin/phpstan', 'analyse']);
}

#[AsTask(name: 'cs-fixer', namespace: 'lint:backend', description: 'Check PHP code style with PHP CS Fixer')]
function cs_fixer(
    #[AsOption(description: 'Apply the fixes')]
    bool $fix = false,
): void {
    io()->section('PHP CS Fixer');
    \exec_in(\App::Backend, ['vendor/bin/php-cs-fixer', 'fix', ...($fix ? [] : ['--dry-run', '--diff'])]);
}

#[AsTask(name: 'rector', namespace: 'lint:backend', description: 'Check PHP code with Rector', aliases: ['rector'])]
function rector(
    #[AsOption(description: 'Apply the fixes')]
    bool $fix = false,
): void {
    io()->section('Rector');
    \exec_in(\App::Backend, ['vendor/bin/rector', 'process', ...($fix ? [] : ['--dry-run'])]);
}

#[AsTask(name: 'container', namespace: 'lint:backend', description: 'Lint the Symfony dependency injection container')]
function container(): void
{
    io()->section('Symfony container');
    \exec_in(\App::Backend, ['bin/console', 'lint:container', '-e', 'prod']);
}

#[AsTask(name: 'doctrine', namespace: 'lint:backend', description: 'Validate the Doctrine mapping')]
function doctrine(): void
{
    io()->section('Doctrine schema');
    \exec_in(\App::Backend, ['bin/console', 'doctrine:schema:validate', '--skip-sync']);
}

#[AsTask(name: 'eslint', namespace: 'lint:frontend', description: 'Lint TypeScript with ESLint')]
function eslint(
    #[AsOption(description: 'Apply the fixes')]
    bool $fix = false,
): void {
    io()->section('ESLint');
    \exec_in(\App::Frontend, ['corepack', 'pnpm', 'run', 'lint', ...($fix ? ['--fix'] : [])]);
}

#[AsTask(name: 'tsc', namespace: 'lint:frontend', description: 'Type-check TypeScript')]
function tsc(): void
{
    io()->section('tsc');
    \exec_in(\App::Frontend, ['corepack', 'pnpm', 'exec', 'tsc', '--noEmit']);
}

#[AsTask(name: 'ruff', namespace: 'lint:ml', description: 'Lint Python with Ruff')]
function ruff(
    #[AsOption(description: 'Apply the fixes')]
    bool $fix = false,
): void {
    io()->section('Ruff');
    \exec_in(\App::Ml, ['uv', 'run', 'ruff', 'check', ...($fix ? ['--fix'] : []), '.']);
}

#[AsTask(name: 'mypy', namespace: 'lint:ml', description: 'Type-check Python with mypy')]
function mypy(): void
{
    io()->section('mypy');
    \exec_in(\App::Ml, ['uv', 'run', 'mypy', '.']);
}

#[AsTask(name: 'backend', description: 'Run every backend linter')]
function backend(
    #[AsOption(description: 'Apply the fixes')]
    bool $fix = false,
): void {
    composer();
    rector($fix);
    cs_fixer($fix);
    phpstan();
    container();
    doctrine();
}

#[AsTask(name: 'frontend', description: 'Run every frontend linter')]
function frontend(
    #[AsOption(description: 'Apply the fixes')]
    bool $fix = false,
): void {
    eslint($fix);
    tsc();
}

#[AsTask(name: 'ml', description: 'Run every ML linter')]
function ml(
    #[AsOption(description: 'Apply the fixes')]
    bool $fix = false,
): void {
    ruff($fix);
    mypy();
}

#[AsTask(name: 'all', description: 'Run every linter', aliases: ['lint'])]
function all(
    #[AsOption(description: 'Apply the fixes')]
    bool $fix = false,
): void {
    backend($fix);
    frontend($fix);
    ml($fix);
}
