<?php

declare(strict_types=1);

namespace App\Service\Licence\Magic;

use App\Service\Licence\Dto\Card;
use App\Service\Licence\Dto\Extension;
use App\Service\Licence\Dto\PriceSet;

/**
 * @phpstan-type ScryfallSet array{code: string, name: string, card_count: int}
 * @phpstan-type ScryfallCard array{
 *     id: string,
 *     name: string,
 *     collector_number: string,
 *     set: string,
 *     set_name: string,
 *     artist?: string|null,
 *     rarity?: string|null,
 *     foil?: bool,
 *     nonfoil?: bool,
 *     image_uris?: array{normal?: string},
 *     card_faces?: list<array{image_uris?: array{normal?: string}}>,
 *     prices?: array{eur?: string|null, eur_foil?: string|null},
 * }
 */
final class MagicNormaliser
{
    /** @param ScryfallSet $set */
    public function normaliseExtension(array $set): Extension
    {
        return new Extension(
            id: $set['code'],
            name: $set['name'],
            totalCards: $set['card_count'],
        );
    }

    /** @param ScryfallCard $card */
    public function normaliseCard(array $card): Card
    {
        return new Card(
            licence: 'magic',
            cardId: "magic-{$card['id']}",
            cardNumber: $card['collector_number'],
            cardName: $card['name'],
            extensionId: $card['set'],
            extensionName: $card['set_name'],
            illustrator: $card['artist'] ?? null,
            rarity: $card['rarity'] ?? null,
            cardImage: $this->extractImage($card),
            variant: $this->extractVariants($card),
            prices: $this->extractPrices($card),
        );
    }

    /**
     * Double-faced cards carry no image at the root, only one per face. We keep the
     * front face until #54 exposes both.
     *
     * @param ScryfallCard $card
     */
    private function extractImage(array $card): ?string
    {
        return $card['image_uris']['normal']
            ?? $card['card_faces'][0]['image_uris']['normal']
            ?? null;
    }

    /**
     * @param ScryfallCard $card
     *
     * @return string[]
     */
    private function extractVariants(array $card): array
    {
        return array_keys(array_filter([
            'nonfoil' => $card['nonfoil'] ?? false,
            'foil' => $card['foil'] ?? false,
        ]));
    }

    /**
     * Scryfall publishes a single price per finish, so low and trend stay null:
     * the source does not track them at all.
     *
     * @param ScryfallCard $card
     *
     * @return array<string, PriceSet>
     */
    private function extractPrices(array $card): array
    {
        $prices = [];

        foreach (['nonfoil' => 'eur', 'foil' => 'eur_foil'] as $variant => $key) {
            $value = $card['prices'][$key] ?? null;

            if (null !== $value) {
                $prices[$variant] = new PriceSet(avg: (float) $value);
            }
        }

        return $prices;
    }
}
