<?php

declare(strict_types=1);

namespace App\Service\Licence\Dto;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\State\CardCollectionProvider;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/licence/{slug}/extensions/{setId}/cards',
            provider: CardCollectionProvider::class
        ),
    ],
)]
final readonly class Card
{
    /**
     * @param string[]                $variant
     * @param array<string, PriceSet> $prices
     */
    public function __construct(
        public string $licence,
        public string $cardId,
        public string $cardNumber,
        public string $cardName,
        public string $extensionId,
        public string $extensionName,
        public ?string $illustrator,
        public ?string $rarity,
        public ?string $cardImage,
        public array $variant,
        public array $prices,
    ) {
    }
}
