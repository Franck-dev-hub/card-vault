<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Service\Licence\Dto\Licence;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @implements ProviderInterface<Licence>
 */
final readonly class LicenceProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire('%kernel.project_dir%/resources/licences.json')]
        private string $licencesFile,
    ) {
    }

    /**
     * @return Licence[]
     */
    public function provide(
        Operation $operation,
        array $uriVariables = [],
        array $context = [],
    ): array {
        $json = file_get_contents($this->licencesFile);

        if (false === $json) {
            throw new \RuntimeException("Unable to read licences file: {$this->licencesFile}");
        }

        /** @var list<array{slug: string, name: string}> $rows */
        $rows = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

        return array_map(
            static fn (array $row) => new Licence(
                slug: $row['slug'],
                name: $row['name'],
            ),
            $rows,
        );
    }
}
