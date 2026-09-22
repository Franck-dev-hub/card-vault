<?php

declare(strict_types=1);

namespace App\Tests\Service\Licence;

use App\Service\Licence\Dto\Extension;
use App\Service\Licence\LicenceClientInterface;
use App\Service\Licence\LicenceClientRegistry;
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

        $licence = $this->createStub(LicenceClientInterface::class);
        $licence->method('listExtensions')->willReturn([new Extension(id: 'base1', name: 'Base Set', totalCards: 102)]);

        self::getContainer()->set(LicenceClientRegistry::class, new LicenceClientRegistry(new ServiceLocator([
            'pokemon' => static fn (): LicenceClientInterface => $licence,
        ])));

        $client->request('GET', '/api/licence/pokemon/extensions', server: ['HTTP_ACCEPT' => 'application/json']);

        self::assertResponseIsSuccessful();
        self::assertJsonStringEqualsJsonString(
            '[{"id":"base1","name":"Base Set","totalCards":102}]',
            (string) $client->getResponse()->getContent(),
        );
    }
}
