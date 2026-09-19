<?php

declare(strict_types=1);

namespace App\Service\Licence;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class UpstreamAwareHttpClient implements ClientInterface
{
    public function __construct(
        private ClientInterface $decorated,
        private string $licenceSlug,
    ) {
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        try {
            $response = $this->decorated->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new UpstreamNotAvailableException($this->licenceSlug, "Upstream for licence \"{$this->licenceSlug}\" is unreachable", $e);
        }

        if ($response->getStatusCode() >= 500) {
            throw new UpstreamNotAvailableException($this->licenceSlug, "Upstream for licence \"{$this->licenceSlug}\" returned {$response->getStatusCode()}");
        }

        return $response;
    }
}
