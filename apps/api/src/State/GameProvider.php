<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Service\Game\Dto\Game;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @implements ProviderInterface<Game>
 */
final readonly class GameProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire('%kernel.project_dir%/resources/games.json')]
        private string $gamesFile,
    ) {
    }

    /**
     * @return Game[]
     */
    public function provide(
        Operation $operation,
        array $uriVariables = [],
        array $context = [],
    ): array {
        $json = file_get_contents($this->gamesFile);

        if (false === $json) {
            throw new \RuntimeException("Unable to read games file: {$this->gamesFile}");
        }

        /** @var list<array{slug: string, name: string}> $rows */
        $rows = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

        return array_map(
            static fn (array $row) => new Game(
                slug: $row['slug'],
                name: $row['name'],
            ),
            $rows,
        );
    }
}
