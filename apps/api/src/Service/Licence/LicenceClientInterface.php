<?php

declare(strict_types=1);

namespace App\Service\Licence;

use App\Service\Licence\Dto\Card;
use App\Service\Licence\Dto\Extension;

interface LicenceClientInterface
{
    /** @return Extension[] */
    public function listExtensions(): array;

    /** @return Card[] */
    public function listCards(string $extensionId): array;

    public function getCard(string $extensionId, string $cardId): Card;
}
