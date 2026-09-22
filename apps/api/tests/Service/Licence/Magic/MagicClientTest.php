<?php

declare(strict_types=1);

namespace App\Tests\Service\Licence\Magic;

use App\Service\Licence\LicenceNotFoundException;
use App\Service\Licence\Magic\MagicClient;
use App\Service\Licence\Magic\MagicNormaliser;
use App\Service\Licence\UpstreamNotAvailableException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class MagicClientTest extends TestCase
{
    /** @param MockResponse[] $responses */
    private function buildClient(array $responses): MagicClient
    {
        return new MagicClient(
            new MockHttpClient($responses, 'https://api.scryfall.com/'),
            new MagicNormaliser(),
        );
    }

    /** @param array<string, mixed> $payload */
    private function json(array $payload, int $status = 200): MockResponse
    {
        return new MockResponse(json_encode($payload, JSON_THROW_ON_ERROR), ['http_code' => $status]);
    }

    public function testListExtensionsMapsEachSet(): void
    {
        $client = $this->buildClient([
            $this->json(['data' => [
                ['code' => 'woe', 'name' => 'Wilds of Eldraine', 'card_count' => 281],
                ['code' => 'ltr', 'name' => 'The Lord of the Rings', 'card_count' => 451],
            ]]),
        ]);

        $extensions = $client->listExtensions();

        self::assertCount(2, $extensions);
        self::assertSame('woe', $extensions[0]->id);
        self::assertSame(451, $extensions[1]->totalCards);
    }

    /**
     * Scryfall caps a search at 175 cards, so the client must follow next_page until
     * has_more turns false, otherwise large sets come back truncated.
     */
    public function testListCardsFollowsEveryPage(): void
    {
        $client = $this->buildClient([
            $this->json([
                'has_more' => true,
                'next_page' => 'https://api.scryfall.com/cards/search?q=set%3Awoe&page=2',
                'data' => [$this->card('first')],
            ]),
            $this->json([
                'has_more' => false,
                'data' => [$this->card('second')],
            ]),
        ]);

        $cards = $client->listCards('woe');

        self::assertCount(2, $cards);
        self::assertSame('magic-first', $cards[0]->cardId);
        self::assertSame('magic-second', $cards[1]->cardId);
    }

    public function testListCardsSearchesTheSetByItsCode(): void
    {
        $response = $this->json(['has_more' => false, 'data' => []]);
        $client = $this->buildClient([$response]);

        $client->listCards('woe');

        self::assertSame('https://api.scryfall.com/cards/search?q=set%3Awoe', $response->getRequestUrl());
    }

    public function testListCardsThrowsWhenExtensionIsUnknown(): void
    {
        $client = $this->buildClient([$this->json([], 404)]);

        $this->expectException(LicenceNotFoundException::class);
        $this->expectExceptionCode(0);

        $client->listCards('does-not-exist');
    }

    public function testGetCardMapsASingleCard(): void
    {
        $client = $this->buildClient([$this->json($this->card('uuid'))]);

        $card = $client->getCard('uuid');

        self::assertSame('magic-uuid', $card->cardId);
        self::assertSame('woe', $card->extensionId);
    }

    public function testUpstreamFailureIsNotReportedAsAMissingCard(): void
    {
        $client = $this->buildClient([$this->json([], 503)]);

        $this->expectException(UpstreamNotAvailableException::class);

        $client->getCard('uuid');
    }

    /** @return array<string, mixed> */
    private function card(string $id): array
    {
        return [
            'id' => $id,
            'name' => 'Agatha of the Vile Cauldron',
            'collector_number' => '199',
            'set' => 'woe',
            'set_name' => 'Wilds of Eldraine',
            'nonfoil' => true,
        ];
    }
}
