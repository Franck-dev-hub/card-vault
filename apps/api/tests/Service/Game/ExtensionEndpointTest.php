<?php

declare(strict_types=1);

namespace App\Tests\Service\Game;

use App\Service\Game\Dto\Extension;
use App\Service\Game\GameClientInterface;
use App\Service\Game\GameClientRegistry;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;

final class ExtensionEndpointTest extends WebTestCase
{
    public function testExtensionsAreListedWithTheirUpstreamId(): void
    {
        $client = self::createClient();

        // Infection mutates classes in memory: cached metadata would hide attribute mutants.
        foreach (['resource', 'resource_collection', 'property'] as $pool) {
            $cache = self::getContainer()->get('api_platform.cache.metadata.'.$pool);
            \assert($cache instanceof CacheItemPoolInterface);
            $cache->clear();
        }

        $game = $this->createStub(GameClientInterface::class);
        $game->method('listExtensions')->willReturn([new Extension(id: 'base1', name: 'Base Set', totalCards: 102)]);

        self::getContainer()->set(GameClientRegistry::class, new GameClientRegistry(new ServiceLocator([
            'pokemon' => static fn (): GameClientInterface => $game,
        ])));

        $client->request('GET', '/api/games/pokemon/extensions', server: ['HTTP_ACCEPT' => 'application/json']);

        self::assertResponseIsSuccessful();
        self::assertJsonStringEqualsJsonString(
            '[{"id":"base1","name":"Base Set","totalCards":102}]',
            (string) $client->getResponse()->getContent(),
        );
    }
}
