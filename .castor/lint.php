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
    \exec_in(\App::Frontend, ['pnpm', 'run', 'lint', ...($fix ? ['--fix'] : [])]);
}

#[AsTask(name: 'tsc', namespace: 'lint:frontend', description: 'Type-check TypeScript')]
function tsc(): void
{
    io()->section('tsc');
    \exec_in(\App::Frontend, ['pnpm', 'exec', 'tsc', '--noEmit']);
}

#[AsTask(name: 'ruff', namespace: 'lint:ml', description: 'Lint Python with Ruff')]
function ruff(
    #[AsOption(description: 'Apply the fixes')]
    bool $fix = false,
): void {
    io()->section('Ruff');
    \exec_in(\App::Ml, ['uv', 'run', 'ruff', 'format', ...($fix ? [] : ['--check']), '.']);
    \exec_in(\App::Ml, ['uv', 'run', 'ruff', 'check', ...($fix ? ['--fix'] : []), '.']);
}

#[AsTask(name: 'mypy', namespace: 'lint:ml', description: 'Type-check Python with mypy')]
function mypy(): void
{
    io()->section('mypy');
    \exec_in(\App::Ml, ['uv', 'run', 'mypy', '.']);
}

#[AsTask(name: 'php-version', namespace: 'lint:backend', description: 'Check that every file declares the same PHP version')]
function php_version(): void
{
    io()->section('PHP version');
    $composer = \dirname(__DIR__) . '/apps/api/composer.json';
    $versions = env_versions('PHP_VERSION') + [
        'composer.json require.php' => read_version($composer, '/"php": ">=(\d+\.\d+)"/'),
        'composer.json config.platform.php' => read_version($composer, '/"platform": \{\s*"php": "(\d+\.\d+)\.\d+"/'),
    ];

    assert_same_version('PHP', $versions);
    io()->success(\sprintf('PHP %s everywhere.', $versions['.env']));
}

#[AsTask(name: 'python-version', namespace: 'lint:ml', description: 'Check that every file declares the same Python version')]
function python_version(): void
{
    io()->section('Python version');
    $pyproject = \dirname(__DIR__) . '/apps/ml/pyproject.toml';
    $versions = env_versions('PYTHON_VERSION') + [
        'apps/ml/pyproject.toml' => read_version($pyproject, '/^requires-python = "==(\d+\.\d+)\.\*"$/m'),
    ];

    assert_same_version('Python', $versions);
    io()->success(\sprintf('Python %s everywhere.', $versions['.env']));
}

#[AsTask(name: 'versions', description: 'Check that every file declares the runtime versions of .env')]
function versions(): void
{
    php_version();
    python_version();
}

/**
 * @return array<string, string|null> env file => version
 */
function env_versions(string $variable): array
{
    $root = \dirname(__DIR__);
    $pattern = '/^' . $variable . '=(\S+)$/m';
    $versions = ['.env' => read_version($root . '/.env', $pattern)];
    // Compose loads .env.local over .env: a stale copy would pin the old version.
    $local = $root . '/.env.local';
    if (is_file($local) && null !== $version = read_version($local, $pattern)) {
        $versions['.env.local'] = $version;
    }

    return $versions;
}

/**
 * @param array<string, string|null> $versions file => version, null when the pattern is not found
 */
function assert_same_version(string $runtime, array $versions): void
{
    $unreadable = array_keys($versions, null, true);
    if ([] !== $unreadable) {
        throw new \RuntimeException(\sprintf('No %s version found in: %s.', $runtime, implode(', ', $unreadable)));
    }

    if (1 !== \count(array_unique($versions))) {
        $found = array_map(static fn (string $file, string $version): string => "{$file} {$version}", array_keys($versions), $versions);

        throw new \RuntimeException(\sprintf('%s versions differ: %s.', $runtime, implode(', ', $found)));
    }
}

function read_version(string $file, string $pattern): ?string
{
    return 1 === preg_match($pattern, (string) file_get_contents($file), $match) ? $match[1] : null;
}

#[AsTask(name: 'backend', description: 'Run every backend linter')]
function backend(
    #[AsOption(description: 'Apply the fixes')]
    bool $fix = false,
): void {
    php_version();
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
    python_version();
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
