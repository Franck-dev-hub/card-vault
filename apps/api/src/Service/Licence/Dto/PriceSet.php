<?php

declare(strict_types=1);

namespace App\Service\Licence\Dto;

final readonly class PriceSet
{
    public function __construct(
        public float $avg,
        public ?float $low = null,
        public ?float $trend = null,
    ) {
    }
}
