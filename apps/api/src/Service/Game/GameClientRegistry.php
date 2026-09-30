<?php

declare(strict_types=1);

namespace App\Service\Game;

use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ServiceLocator;

final readonly class GameClientRegistry
{
    /**
     * @param ServiceLocator<GameClientInterface> $locator
     */
    public function __construct(
        #[AutowireLocator('app.game_client', indexAttribute: 'slug')]
        private ServiceLocator $locator,
    ) {
    }

    public function get(string $slug): GameClientInterface
    {
        if ($this->locator->has($slug)) {
            return $this->locator->get($slug);
        }
        throw new GameNotFoundException('Game not found: '.$slug);
    }
}
