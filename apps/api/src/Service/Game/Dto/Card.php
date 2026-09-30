<?php

declare(strict_types=1);

namespace App\Service\Game\Dto;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use App\State\CardCollectionProvider;
use App\State\CardItemProvider;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/games/{slug}/extensions/{extensionId}/cards',
            provider: CardCollectionProvider::class
        ),
        new Get(
            uriTemplate: '/games/{slug}/cards/{cardId}',
            uriVariables: [
                'slug' => new Link(parameterName: 'slug', fromClass: Card::class, identifiers: ['game']),
                'cardId' => new Link(parameterName: 'cardId', fromClass: Card::class, identifiers: ['cardId']),
            ],
            provider: CardItemProvider::class
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
        public string $game,
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
