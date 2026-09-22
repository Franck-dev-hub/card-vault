<?php

declare(strict_types=1);

namespace docker;

use Castor\Attribute\AsArgument;
use Castor\Attribute\AsOption;
use Castor\Attribute\AsTask;
use Symfony\Component\Dotenv\Dotenv;

use function Castor\context;
use function Castor\io;

const SERVICES = ['api', 'frontend', 'ml', 'database', 'redis-cache', 'redis-session', 'caddy'];

#[AsTask(name: 'up', description: 'Start the dev stack', aliases: ['up'])]
function up(): void
{
    \docker_compose(['up', '-d']);
    display_urls();
}

#[AsTask(name: 'stop', description: 'Stop the dev stack', aliases: ['stop'])]
function stop(): void
{
    \docker_compose(['stop']);
}

#[AsTask(name: 'build', description: 'Build and start the dev stack, or rebuild one service')]
function build(
    #[AsOption(description: 'Rebuild and restart this service only', autocomplete: SERVICES)]
    ?string $service = null,
): void {
    if (null !== $service) {
        \docker_compose(['up', '--build', '--no-deps', '-d', $service]);

        return;
    }

    \docker_compose(['up', '--build', '-d']);
    display_urls();
}

#[AsTask(name: 'terminal', description: 'Open a shell in a dev container', aliases: ['terminal'])]
function terminal(
    #[AsArgument(description: 'Service to enter', autocomplete: SERVICES)]
    string $service = 'api',
): void {
    $shell = 'frontend' === $service ? 'sh' : 'bash';

    \docker_compose(['exec', $service, $shell], context()->withTty()->withAllowFailure());
}

function display_urls(): void
{
    $env = (new Dotenv())->parse((string) file_get_contents(\dirname(__DIR__) . '/.env'));
    $domain = $env['PROJECT_DOMAIN'];

    io()->success('The dev stack is running');
    io()->definitionList(
        ['Frontend' => 'http://' . $domain],
        ['API' => 'http://' . $domain . '/api'],
        ['ML' => 'http://' . $domain . '/ml'],
        ['PgAdmin' => 'http://localhost:5050'],
        ['RedisInsight' => 'http://localhost:5540'],
        ['Mailpit' => 'http://localhost:8025'],
        ['Postgres' => 'postgresql://localhost:5432'],
        ['Redis cache' => 'redis://localhost:6379'],
        ['Redis session' => 'redis://localhost:6380'],
    );
}
