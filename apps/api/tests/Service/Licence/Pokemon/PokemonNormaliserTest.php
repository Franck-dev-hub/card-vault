<?php

declare(strict_types=1);

namespace App\Tests\Service\Licence\Pokemon;

use App\Service\Licence\Pokemon\PokemonNormaliser;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Psr18Client;
use TCGdex\Model\Card as SdkCard;
use TCGdex\Model\Model;
use TCGdex\Model\SetResume;
use TCGdex\TCGdex;

final class PokemonNormaliserTest extends TestCase
{
    private TCGdex $sdk;

    private PokemonNormaliser $normaliser;

    protected function setUp(): void
    {
        $psr17Factory = new Psr17Factory();
        TCGdex::$requestFactory = $psr17Factory;
        TCGdex::$responseFactory = $psr17Factory;
        TCGdex::$client = new Psr18Client();

        $this->sdk = new TCGdex('en');
        $this->normaliser = new PokemonNormaliser();
    }

    /**
     * @param array<string, mixed> $data
     */
    private function fixture(array $data): object
    {
        $json = json_encode($data, JSON_THROW_ON_ERROR);
        $decoded = json_decode($json, false, flags: JSON_THROW_ON_ERROR);

        assert(is_object($decoded));

        return $decoded;
    }

    public function testNormaliseExtensionMapsFields(): void
    {
        $sdkSet = Model::build(new SetResume($this->sdk), $this->fixture([
            'id' => 'swsh3',
            'name' => 'Darkness Ablaze',
            'cardCount' => ['total' => 201, 'official' => 189],
        ]));
        self::assertNotNull($sdkSet);

        $extension = $this->normaliser->normaliseExtension($sdkSet);

        self::assertSame('swsh3', $extension->id);
        self::assertSame('Darkness Ablaze', $extension->name);
        self::assertSame(201, $extension->totalCards);
    }

    #[IgnoreDeprecations]
    public function testNormaliseCardMapsFieldsAndPerVariantPrices(): void
    {
        $sdkCard = Model::build(new SdkCard($this->sdk), $this->fixture([
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
            'variants_detailed' => [
                [
                    'type' => 'normal',
                    'pricing' => ['cardmarket' => ['avg' => 0.08, 'low' => 0.02, 'trend' => 0.1]],
                ],
                [
                    'type' => 'reverse',
                    'pricing' => ['cardmarket' => ['avg' => 0.46, 'low' => 0.17, 'trend' => 0.3]],
                ],
            ],
        ]));
        self::assertNotNull($sdkCard);

        $card = $this->normaliser->normaliseCard($sdkCard);

        self::assertSame('pokemon', $card->licence);
        self::assertSame('pokemon-swsh3-136', $card->cardId);
        self::assertSame('136', $card->cardNumber);
        self::assertSame('Furret', $card->cardName);
        self::assertSame('swsh3', $card->extensionId);
        self::assertSame('Darkness Ablaze', $card->extensionName);
        self::assertSame('tetsuya koizumi', $card->illustrator);
        self::assertSame('Uncommon', $card->rarity);
        self::assertSame('https://assets.tcgdex.net/en/swsh/swsh3/136', $card->cardImage);
        self::assertSame(['normal', 'reverse'], $card->variant);

        self::assertArrayHasKey('normal', $card->prices);
        self::assertArrayHasKey('reverse', $card->prices);
        self::assertSame(0.08, $card->prices['normal']->avg);
        self::assertSame(0.02, $card->prices['normal']->low);
        self::assertSame(0.1, $card->prices['normal']->trend);
        self::assertSame(0.46, $card->prices['reverse']->avg);
    }

    #[IgnoreDeprecations]
    public function testNormaliseCardHandlesMissingImageIllustratorAndPricing(): void
    {
        $sdkCard = Model::build(new SdkCard($this->sdk), $this->fixture([
            'id' => 'base1-1',
            'localId' => '1',
            'name' => 'Alakazam',
            'image' => null,
            'illustrator' => null,
            'rarity' => 'Rare Holo',
            'set' => ['id' => 'base1', 'name' => 'Base'],
            'variants' => [
                'normal' => false,
                'reverse' => false,
                'holo' => true,
                'firstEdition' => false,
                'wPromo' => false,
            ],
        ]));
        self::assertNotNull($sdkCard);

        $card = $this->normaliser->normaliseCard($sdkCard);

        self::assertNull($card->cardImage);
        self::assertNull($card->illustrator);
        self::assertSame(['holo'], $card->variant);
        self::assertSame([], $card->prices);
    }
}
