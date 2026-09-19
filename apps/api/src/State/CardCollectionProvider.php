<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Service\Licence\Dto\Card;
use App\Service\Licence\LicenceClientRegistry;

/**
 * @implements ProviderInterface<Card>
 */
final readonly class CardCollectionProvider implements ProviderInterface
{
    public function __construct(
        private LicenceClientRegistry $registry,
    ) {
    }

    /**
     * @return Card[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $slug = $uriVariables['slug'];
        $setId = $uriVariables['setId'];
        assert(is_string($slug) && is_string($setId));

        return $this->registry->get($slug)->listCards($setId);
    }
}
