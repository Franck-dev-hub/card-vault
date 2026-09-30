<?php

declare(strict_types=1);

namespace App\Tests\Service\Game;

use App\Service\Game\GameClientRegistry;
use App\Service\Game\GameNotFoundException;
use App\Service\Game\Pokemon\PokemonClient;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class GameClientRegistryTest extends KernelTestCase
{
    public function testKnownSlugResolvesToTheTaggedClient(): void
    {
        self::bootKernel();

        $registry = self::getContainer()->get(GameClientRegistry::class);

        self::assertInstanceOf(PokemonClient::class, $registry->get('pokemon'));
    }

    public function testUnknownSlugThrows(): void
    {
        self::bootKernel();

        $registry = self::getContainer()->get(GameClientRegistry::class);

        $this->expectException(GameNotFoundException::class);
        $this->expectExceptionMessageIs('Game not found: does-not-exist');

        $registry->get('does-not-exist');
    }
}
