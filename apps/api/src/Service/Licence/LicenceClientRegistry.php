<?php

declare(strict_types=1);

namespace App\Service\Licence;

use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ServiceLocator;

final readonly class LicenceClientRegistry
{
    /**
     * @param ServiceLocator<LicenceClientInterface> $locator
     */
    public function __construct(
        #[AutowireLocator('app.licence_client', indexAttribute: 'slug')]
        private ServiceLocator $locator,
    ) {
    }

    public function get(string $slug): LicenceClientInterface
    {
        if ($this->locator->has($slug)) {
            return $this->locator->get($slug);
        }
        throw new LicenceNotFoundException('Licence not found: '.$slug);
    }
}
