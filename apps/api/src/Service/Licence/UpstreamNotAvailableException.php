<?php

declare(strict_types=1);

namespace App\Service\Licence;

final class UpstreamNotAvailableException extends LicenceClientException
{
    public function __construct(
        public readonly string $licenceSlug,
        string $message,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, previous: $previous);
    }
}
