<?php

declare(strict_types=1);

namespace App\Tests\Service\Licence\Pokemon;

use App\Service\Licence\LicenceNotFoundException;
use App\Service\Licence\Pokemon\PokemonClient;
use App\Service\Licence\Pokemon\PokemonNormaliser;
use App\Service\Licence\UpstreamAwareHttpClient;
use App\Service\Licence\UpstreamNotAvailableException;
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
        TCGdex::$client = new UpstreamAwareHttpClient(new Psr18Client($httpClient), 'pokemon');
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

    /**
     * Listing cards costs one call for the set, then one per card, because the set
     * endpoint only returns partial cards and the DTO must stay complete.
     */
    #[IgnoreDeprecations]
    public function testListCardsFetchesEveryCardOfTheSet(): void
    {
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'id' => 'base1',
                'name' => 'Base Set',
                'cardCount' => ['total' => 1, 'official' => 1],
                'cards' => [
                    ['id' => 'base1-1', 'localId' => '1', 'name' => 'Alakazam', 'image' => null],
                ],
            ], JSON_THROW_ON_ERROR)),
            new MockResponse(json_encode([
                'id' => 'base1-1',
                'localId' => '1',
                'name' => 'Alakazam',
                'image' => 'https://assets.tcgdex.net/en/base/base1/1',
                'illustrator' => 'Ken Sugimori',
                'rarity' => 'Rare Holo',
                'set' => ['id' => 'base1', 'name' => 'Base Set'],
                'variants' => [
                    'normal' => false,
                    'reverse' => false,
                    'holo' => true,
                    'firstEdition' => true,
                    'wPromo' => false,
                ],
            ], JSON_THROW_ON_ERROR)),
        ]);

        $cards = $this->buildClient($httpClient)->listCards('base1');

        self::assertCount(1, $cards);
        self::assertSame('pokemon-base1-1', $cards[0]->cardId);
        self::assertSame('Alakazam', $cards[0]->cardName);
        self::assertSame('base1', $cards[0]->extensionId);
        self::assertSame('Ken Sugimori', $cards[0]->illustrator);
        self::assertSame(['holo', 'firstEdition'], $cards[0]->variant);
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

        $card = $this->buildClient($httpClient)->getCard('swsh3-136');

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

        $this->buildClient($httpClient)->getCard('does-not-exist');
    }

    public function testUpstreamFailureIsNotReportedAsAMissingCard(): void
    {
        $httpClient = new MockHttpClient([
            new MockResponse('', ['http_code' => 503]),
        ]);

        $this->expectException(UpstreamNotAvailableException::class);

        $this->buildClient($httpClient)->getCard('swsh3-136');
    }
}
