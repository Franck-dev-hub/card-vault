<?php

declare(strict_types=1);

namespace App\Tests\Service\Licence;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Service\Licence\Dto\Card;
use App\Service\Licence\Dto\Extension;
use App\Service\Licence\LicenceClientInterface;
use App\Service\Licence\LicenceClientRegistry;
use App\State\CardCollectionProvider;
use App\State\CardItemProvider;
use App\State\ExtensionProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;

final class StateProviderTest extends TestCase
{
    private function registryFor(LicenceClientInterface $client): LicenceClientRegistry
    {
        return new LicenceClientRegistry(new ServiceLocator([
            'pokemon' => static fn (): LicenceClientInterface => $client,
        ]));
    }

    public function testExtensionProviderDelegatesToTheLicenceClient(): void
    {
        $extension = new Extension(id: 'base1', name: 'Base Set', totalCards: 102);

        $client = $this->createMock(LicenceClientInterface::class);
        $client->expects(self::once())->method('listExtensions')->willReturn([$extension]);

        $provider = new ExtensionProvider($this->registryFor($client));

        self::assertSame([$extension], $provider->provide(new GetCollection(), ['slug' => 'pokemon']));
    }

    public function testCardCollectionProviderPassesTheExtensionId(): void
    {
        $card = $this->card();

        $client = $this->createMock(LicenceClientInterface::class);
        $client->expects(self::once())->method('listCards')->with('base1')->willReturn([$card]);

        $provider = new CardCollectionProvider($this->registryFor($client));

        self::assertSame(
            [$card],
            $provider->provide(new GetCollection(), ['slug' => 'pokemon', 'setId' => 'base1']),
        );
    }

    public function testCardItemProviderPassesTheCardId(): void
    {
        $card = $this->card();

        $client = $this->createMock(LicenceClientInterface::class);
        $client->expects(self::once())->method('getCard')->with('base1-1')->willReturn($card);

        $provider = new CardItemProvider($this->registryFor($client));

        self::assertSame(
            $card,
            $provider->provide(new Get(), ['slug' => 'pokemon', 'cardId' => 'base1-1']),
        );
    }

    public function testCardItemProviderStripsTheLicencePrefixBeforeCallingUpstream(): void
    {
        $card = $this->card();

        $client = $this->createMock(LicenceClientInterface::class);
        $client->expects(self::once())->method('getCard')->with('base1-1')->willReturn($card);

        $provider = new CardItemProvider($this->registryFor($client));

        $provider->provide(new Get(), ['slug' => 'pokemon', 'cardId' => 'pokemon-base1-1']);
    }

    private function card(): Card
    {
        return new Card(
            licence: 'pokemon',
            cardId: 'pokemon-base1-1',
            cardNumber: '1',
            cardName: 'Alakazam',
            extensionId: 'base1',
            extensionName: 'Base Set',
            illustrator: null,
            rarity: 'Rare Holo',
            cardImage: null,
            variant: ['holo'],
            prices: [],
        );
    }
}
