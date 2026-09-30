<?php

declare(strict_types=1);

namespace App\Service\Game;

use App\Service\Game\Dto\Card;
use App\Service\Game\Dto\Extension;

interface GameClientInterface
{
    /** @return Extension[] */
    public function listExtensions(): array;

    /** @return Card[] */
    public function listCards(string $extensionId): array;

    public function getCard(string $cardId): Card;
}
