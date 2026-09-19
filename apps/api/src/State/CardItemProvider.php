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
final readonly class CardItemProvider implements ProviderInterface
{
    public function __construct(
        private LicenceClientRegistry $registry,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Card
    {
        $slug = $uriVariables['slug'];
        $cardId = $uriVariables['cardId'];
        assert(is_string($slug) && is_string($cardId));

        $upstreamId = str_starts_with($cardId, "{$slug}-")
            ? substr($cardId, \strlen($slug) + 1)
            : $cardId;

        return $this->registry->get($slug)->getCard($upstreamId);
    }
}
