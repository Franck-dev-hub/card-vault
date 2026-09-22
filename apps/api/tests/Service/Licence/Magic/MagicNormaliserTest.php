<?php

declare(strict_types=1);

namespace App\Tests\Service\Licence\Magic;

use App\Service\Licence\Magic\MagicNormaliser;
use PHPUnit\Framework\TestCase;

final class MagicNormaliserTest extends TestCase
{
    private MagicNormaliser $normaliser;

    protected function setUp(): void
    {
        $this->normaliser = new MagicNormaliser();
    }

    public function testNormaliseExtensionUsesTheSetCodeAsIdentifier(): void
    {
        $extension = $this->normaliser->normaliseExtension([
            'code' => 'woe',
            'name' => 'Wilds of Eldraine',
            'card_count' => 281,
        ]);

        // The code, not the Scryfall uuid: it is what the card search expects back.
        self::assertSame('woe', $extension->id);
        self::assertSame('Wilds of Eldraine', $extension->name);
        self::assertSame(281, $extension->totalCards);
    }

    public function testNormaliseCardMapsFieldsVariantsAndPrices(): void
    {
        $card = $this->normaliser->normaliseCard([
            'id' => 'd6c48f07-63b7-4a60-8da6-ce77405abf1e',
            'name' => 'Agatha of the Vile Cauldron',
            'collector_number' => '199',
            'set' => 'woe',
            'set_name' => 'Wilds of Eldraine',
            'artist' => 'Jason A. Engle',
            'rarity' => 'mythic',
            'foil' => true,
            'nonfoil' => true,
            'image_uris' => ['normal' => 'https://cards.scryfall.io/normal/front/d/6/agatha.jpg'],
            'prices' => ['eur' => '1.63', 'eur_foil' => '2.07'],
        ]);

        self::assertSame('magic', $card->licence);
        self::assertSame('magic-d6c48f07-63b7-4a60-8da6-ce77405abf1e', $card->cardId);
        self::assertSame('199', $card->cardNumber);
        self::assertSame('woe', $card->extensionId);
        self::assertSame('Jason A. Engle', $card->illustrator);
        self::assertSame('https://cards.scryfall.io/normal/front/d/6/agatha.jpg', $card->cardImage);
        self::assertSame(['nonfoil', 'foil'], $card->variant);

        self::assertSame(1.63, $card->prices['nonfoil']->avg);
        self::assertSame(2.07, $card->prices['foil']->avg);
    }

    public function testScryfallPricesCarryNoLowOrTrend(): void
    {
        $card = $this->normaliser->normaliseCard([
            'id' => 'uuid',
            'name' => 'Agatha',
            'collector_number' => '199',
            'set' => 'woe',
            'set_name' => 'Wilds of Eldraine',
            'nonfoil' => true,
            'prices' => ['eur' => '1.63'],
        ]);

        // null, not 0.0: Scryfall does not track these metrics at all.
        self::assertNull($card->prices['nonfoil']->low);
        self::assertNull($card->prices['nonfoil']->trend);
    }

    public function testDoubleFacedCardFallsBackToTheFrontFaceImage(): void
    {
        $card = $this->normaliser->normaliseCard([
            'id' => 'uuid',
            'name' => 'Delver of Secrets // Insectile Aberration',
            'collector_number' => '51',
            'set' => 'isd',
            'set_name' => 'Innistrad',
            'nonfoil' => true,
            'card_faces' => [
                ['image_uris' => ['normal' => 'https://cards.scryfall.io/normal/front/delver.jpg']],
                ['image_uris' => ['normal' => 'https://cards.scryfall.io/normal/back/aberration.jpg']],
            ],
        ]);

        self::assertSame('https://cards.scryfall.io/normal/front/delver.jpg', $card->cardImage);
    }

    public function testRootImageWinsOverFaceImages(): void
    {
        $card = $this->normaliser->normaliseCard([
            'id' => 'uuid',
            'name' => 'Brisela, Voice of Nightmares',
            'collector_number' => '15',
            'set' => 'emn',
            'set_name' => 'Eldritch Moon',
            'nonfoil' => true,
            'image_uris' => ['normal' => 'https://cards.scryfall.io/normal/front/brisela.jpg'],
            'card_faces' => [
                ['image_uris' => ['normal' => 'https://cards.scryfall.io/normal/front/bruna.jpg']],
            ],
        ]);

        self::assertSame('https://cards.scryfall.io/normal/front/brisela.jpg', $card->cardImage);
    }

    public function testCardWithoutPricesOrImageStaysValid(): void
    {
        $card = $this->normaliser->normaliseCard([
            'id' => 'uuid',
            'name' => 'Token',
            'collector_number' => '1',
            'set' => 'ttrk',
            'set_name' => 'Star Trek Tokens',
        ]);

        self::assertNull($card->cardImage);
        self::assertNull($card->illustrator);
        self::assertSame([], $card->variant);
        self::assertSame([], $card->prices);
    }
}
