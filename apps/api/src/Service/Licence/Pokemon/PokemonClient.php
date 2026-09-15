<?php

declare(strict_types=1);

namespace App\Service\Licence\Pokemon;

use App\Service\Licence\Dto\Card;
use App\Service\Licence\Dto\Extension;
use App\Service\Licence\LicenceClientInterface;
use App\Service\Licence\LicenceNotFoundException;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use TCGdex\Model\SetResume;
use TCGdex\TCGdex;

#[AutoconfigureTag('app.licence_client', ['slug' => 'pokemon'])]
final readonly class PokemonClient implements LicenceClientInterface
{
    public function __construct(
        private TCGdex $tcgdex,
        private PokemonNormaliser $normaliser,
    ) {
    }

    /** @return Extension[] */
    public function listExtensions(): array
    {
        /** @var SetResume[] $sets */
        $sets = $this->tcgdex->set->list();

        return array_map(
            $this->normaliser->normaliseExtension(...),
            $sets,
        );
    }

    /** @return Card[] */
    public function listCards(string $extensionId): array
    {
        $set = $this->tcgdex->set->get($extensionId);

        if (null === $set) {
            throw new LicenceNotFoundException("Unknown extension: {$extensionId}");
        }

        return array_map(
            fn ($cardResume) => $this->normaliser->normaliseCard($cardResume->toCard()),
            $set->cards,
        );
    }

    public function getCard(string $extensionId, string $cardId): Card
    {
        $card = $this->tcgdex->card->get($cardId);

        if (null === $card) {
            throw new LicenceNotFoundException("Unknown card: {$cardId}");
        }

        return $this->normaliser->normaliseCard($card);
    }
}
