<?php

declare(strict_types=1);

namespace App\Service\Game;

final class UpstreamNotAvailableException extends GameClientException
{
    public function __construct(
        public readonly string $gameSlug,
        string $message,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, previous: $previous);
    }
}
