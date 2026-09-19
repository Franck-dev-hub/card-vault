<?php

declare(strict_types=1);

namespace App\Service\Licence\Pokemon;

use App\Service\Licence\Dto\Card;
use App\Service\Licence\Dto\Extension;
use App\Service\Licence\Dto\PriceSet;
use TCGdex\Model\Card as SdkCard;
use TCGdex\Model\SetResume;

final class PokemonNormaliser
{
    public function normaliseExtension(SetResume $sdkSet): Extension
    {
        return new Extension(
            id: $sdkSet->id,
            name: $sdkSet->name,
            totalCards: $sdkSet->cardCount->total,
        );
    }

    public function normaliseCard(SdkCard $sdkCard): Card
    {
        return new Card(
            licence: 'pokemon',
            cardId: "pokemon-{$sdkCard->id}",
            cardNumber: $sdkCard->localId,
            cardName: $sdkCard->name,
            extensionId: $sdkCard->set->id,
            extensionName: $sdkCard->set->name,
            illustrator: $sdkCard->illustrator,
            rarity: $sdkCard->rarity,
            cardImage: $sdkCard->image,
            variant: $this->extractVariants($sdkCard),
            prices: $this->extractPrices($sdkCard),
        );
    }

    /** @return string[] */
    private function extractVariants(SdkCard $sdkCard): array
    {
        return array_keys(array_filter(get_object_vars($sdkCard->variants)));
    }

    /** @return array<string, PriceSet> */
    private function extractPrices(SdkCard $sdkCard): array
    {
        /** @var list<object{type: string, pricing: object{cardmarket: object{avg: float, low: float, trend: float}|null}|null}> $variantsDetailed */
        $variantsDetailed = $sdkCard->variants_detailed ?? [];

        $prices = [];
        foreach ($variantsDetailed as $variantDetail) {
            $cardmarket = $variantDetail->pricing->cardmarket ?? null;

            if (null === $cardmarket) {
                continue;
            }

            $prices[$variantDetail->type] = new PriceSet(
                avg: (float) ($cardmarket->avg ?? 0.0),
                low: (float) ($cardmarket->low ?? 0.0),
                trend: (float) ($cardmarket->trend ?? 0.0),
            );
        }

        return $prices;
    }
}
