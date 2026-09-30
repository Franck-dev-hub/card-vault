<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Service\Game\Dto\Card;
use App\Service\Game\GameClientRegistry;

/**
 * @implements ProviderInterface<Card>
 */
final readonly class CardCollectionProvider implements ProviderInterface
{
    public function __construct(
        private GameClientRegistry $registry,
    ) {
    }

    /**
     * @return Card[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $slug = $uriVariables['slug'];
        $extensionId = $uriVariables['extensionId'];
        assert(is_string($slug) && is_string($extensionId));

        return $this->registry->get($slug)->listCards($extensionId);
    }
}
