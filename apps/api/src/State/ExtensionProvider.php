<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Service\Licence\Dto\Extension;
use App\Service\Licence\LicenceClientRegistry;

/**
 * @implements ProviderInterface<Extension>
 */
final readonly class ExtensionProvider implements ProviderInterface
{
    public function __construct(
        private LicenceClientRegistry $registry,
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
