<?php

declare(strict_types=1);

namespace App\Service\Licence\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\State\ExtensionProvider;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/licence/{slug}/extensions',
            provider: ExtensionProvider::class
        ),
    ],
)]
final readonly class Extension
{
    public function __construct(
        #[ApiProperty(identifier: false)]
        public string $id,
        public string $name,
        public ?int $totalCards = null,
    ) {
    }
}
