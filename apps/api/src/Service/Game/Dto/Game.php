<?php

declare(strict_types=1);

namespace App\Service\Game\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\State\GameProvider;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/games',
            provider: GameProvider::class
        ),
    ],
)]
final readonly class Game
{
    public function __construct(
        #[ApiProperty(identifier: true)]
        public string $slug,
        public string $name,
    ) {
    }
}
