<?php

declare(strict_types=1);

namespace App\Service\Licence\Dto;

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
