<?php

declare(strict_types=1);

namespace App\Tests\Service\Licence;

use App\Service\Licence\LicenceClientRegistry;
use App\Service\Licence\LicenceNotFoundException;
use App\Service\Licence\Pokemon\PokemonClient;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class LicenceClientRegistryTest extends KernelTestCase
{
    public function testKnownSlugResolvesToTheTaggedClient(): void
    {
        self::bootKernel();

        $registry = self::getContainer()->get(LicenceClientRegistry::class);

        self::assertInstanceOf(PokemonClient::class, $registry->get('pokemon'));
    }

    public function testUnknownSlugThrows(): void
    {
        self::bootKernel();

        $registry = self::getContainer()->get(LicenceClientRegistry::class);

        $this->expectException(LicenceNotFoundException::class);
        $this->expectExceptionMessageIs('Licence not found: does-not-exist');

        $registry->get('does-not-exist');
    }
}
