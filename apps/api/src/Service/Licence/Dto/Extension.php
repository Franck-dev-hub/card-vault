<?php

declare(strict_types=1);

namespace App\Service\Licence\Dto;

final readonly class Extension
{
    public function __construct(
        public string $id,
        public string $name,
        public ?int $totalCards = null,
    ) {}
}
