<?php

declare(strict_types=1);

namespace App\Tests\Service\Licence\Pokemon;

use App\Service\Licence\LicenceNotFoundException;
use App\Service\Licence\Pokemon\PokemonClient;
use App\Service\Licence\Pokemon\PokemonNormaliser;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;
use TCGdex\TCGdex;

final class PokemonClientTest extends TestCase
{
    private function buildClient(MockHttpClient $httpClient): PokemonClient
    {
        $psr17Factory = new Psr17Factory();
        TCGdex::$requestFactory = $psr17Factory;
        TCGdex::$responseFactory = $psr17Factory;
        TCGdex::$client = new Psr18Client($httpClient);
        TCGdex::$cache = new Psr16Cache(new ArrayAdapter());

        $sdk = new TCGdex('en');

        return new PokemonClient($sdk, new PokemonNormaliser());
    }

    public function testListExtensionsMapsEachSet(): void
    {
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                ['id' => 'base1', 'name' => 'Base', 'cardCount' => ['total' => 102, 'official' => 102]],
                ['id' => 'swsh3', 'name' => 'Darkness Ablaze', 'cardCount' => ['total' => 201, 'official' => 189]],
            ], JSON_THROW_ON_ERROR)),
        ]);

        $extensions = $this->buildClient($httpClient)->listExtensions();

        self::assertCount(2, $extensions);
        self::assertSame('base1', $extensions[0]->id);
        self::assertSame('swsh3', $extensions[1]->id);
        self::assertSame(201, $extensions[1]->totalCards);
    }

    public function testListCardsThrowsWhenExtensionIsUnknown(): void
    {
        $httpClient = new MockHttpClient([
            new MockResponse('', ['http_code' => 404]),
        ]);

        $this->expectException(LicenceNotFoundException::class);

        $this->buildClient($httpClient)->listCards('does-not-exist');
    }

    #[IgnoreDeprecations]
    public function testGetCardMapsAFullCard(): void
    {
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'id' => 'swsh3-136',
                'localId' => '136',
                'name' => 'Furret',
                'image' => 'https://assets.tcgdex.net/en/swsh/swsh3/136',
                'illustrator' => 'tetsuya koizumi',
                'rarity' => 'Uncommon',
                'set' => ['id' => 'swsh3', 'name' => 'Darkness Ablaze'],
                'variants' => [
                    'normal' => true,
                    'reverse' => true,
                    'holo' => false,
                    'firstEdition' => false,
                    'wPromo' => false,
                ],
            ], JSON_THROW_ON_ERROR)),
        ]);

        $card = $this->buildClient($httpClient)->getCard('swsh3', 'swsh3-136');

        self::assertSame('pokemon-swsh3-136', $card->cardId);
        self::assertSame('Furret', $card->cardName);
        self::assertSame(['normal', 'reverse'], $card->variant);
    }

    public function testGetCardThrowsWhenCardIsUnknown(): void
    {
        $httpClient = new MockHttpClient([
            new MockResponse('', ['http_code' => 404]),
        ]);

        $this->expectException(LicenceNotFoundException::class);

        $this->buildClient($httpClient)->getCard('swsh3', 'does-not-exist');
    }
}
