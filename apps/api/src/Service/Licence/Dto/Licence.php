<?php

declare(strict_types=1);

namespace App\Service\Licence\Dto;

final readonly class Licence
{
    public function __construct(
        public string $slug,
        public string $name,
    ) {}
}
