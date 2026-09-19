<?php

declare(strict_types=1);

namespace App\Service\Licence\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\State\LicenceProvider;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/licence',
            provider: LicenceProvider::class
        ),
    ],
)]
final readonly class Licence
{
    public function __construct(
        #[ApiProperty(identifier: true)]
        public string $slug,
        public string $name,
    ) {
    }
}
