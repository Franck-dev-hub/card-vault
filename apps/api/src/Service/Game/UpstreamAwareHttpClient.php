<?php

declare(strict_types=1);

namespace App\Service\Game;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final readonly class UpstreamAwareHttpClient implements ClientInterface
{
    public function __construct(
        private ClientInterface $decorated,
        private string $gameSlug,
    ) {
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        try {
            $response = $this->decorated->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new UpstreamNotAvailableException($this->gameSlug, "Upstream for game \"{$this->gameSlug}\" is unreachable", $e);
        }

        if ($response->getStatusCode() >= 500) {
            throw new UpstreamNotAvailableException($this->gameSlug, "Upstream for game \"{$this->gameSlug}\" returned {$response->getStatusCode()}");
        }

        return $response;
    }
}
