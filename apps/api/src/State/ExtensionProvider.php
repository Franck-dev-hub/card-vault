<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Service\Game\Dto\Extension;
use App\Service\Game\GameClientRegistry;

/**
 * @implements ProviderInterface<Extension>
 */
final readonly class ExtensionProvider implements ProviderInterface
{
    public function __construct(
        private GameClientRegistry $registry,
    ) {
    }

    /**
     * @return Extension[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $slug = $uriVariables['slug'];
        assert(is_string($slug));

        return $this->registry->get($slug)->listExtensions();
    }
}
