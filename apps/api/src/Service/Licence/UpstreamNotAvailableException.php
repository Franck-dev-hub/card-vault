<?php

declare(strict_types=1);

namespace App\Service\Licence;

final class UpstreamNotAvailableException extends LicenceClientException
{
    public function __construct(public readonly string $licenceSlug, string $message)
    {
        parent::__construct($message);
    }
}
