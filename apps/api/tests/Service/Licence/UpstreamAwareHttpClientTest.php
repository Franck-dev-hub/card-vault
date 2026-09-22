<?php

declare(strict_types=1);

namespace App\Tests\Service\Licence;

use App\Service\Licence\UpstreamAwareHttpClient;
use App\Service\Licence\UpstreamNotAvailableException;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class UpstreamAwareHttpClientTest extends TestCase
{
    private function clientReturning(ResponseInterface $response): ClientInterface
    {
        return new class($response) implements ClientInterface {
            public function __construct(private readonly ResponseInterface $response)
            {
            }

            public function sendRequest(RequestInterface $request): ResponseInterface
            {
                return $this->response;
            }
        };
    }

    private function clientThrowing(\Throwable $error): ClientInterface
    {
        return new class($error) implements ClientInterface {
            public function __construct(private readonly \Throwable $error)
            {
            }

            public function sendRequest(RequestInterface $request): ResponseInterface
            {
                throw $this->error;
            }
        };
    }

    public function testSuccessfulResponsePassesThroughUntouched(): void
    {
        $response = new Response(200, [], '{"id":"base1"}');

        $client = new UpstreamAwareHttpClient($this->clientReturning($response), 'pokemon');

        self::assertSame($response, $client->sendRequest(new Request('GET', '/sets')));
    }

    /**
     * A missing card must stay a 404 so the SDK turns it into null, which the licence
     * client reports as LicenceNotFoundException rather than an outage.
     */
    public function testClientErrorPassesThroughUntouched(): void
    {
        $response = new Response(404);

        $client = new UpstreamAwareHttpClient($this->clientReturning($response), 'pokemon');

        self::assertSame($response, $client->sendRequest(new Request('GET', '/cards/nope')));
    }

    public function testServerErrorIsReportedAsAnOutage(): void
    {
        $client = new UpstreamAwareHttpClient($this->clientReturning(new Response(503)), 'pokemon');

        try {
            $client->sendRequest(new Request('GET', '/sets'));
            self::fail('Expected an UpstreamNotAvailableException.');
        } catch (UpstreamNotAvailableException $e) {
            self::assertSame('pokemon', $e->licenceSlug);
            self::assertStringContainsString('503', $e->getMessage());
        }
    }

    public function testStatus500IsAlreadyAnOutage(): void
    {
        $client = new UpstreamAwareHttpClient($this->clientReturning(new Response(500)), 'pokemon');

        $this->expectException(UpstreamNotAvailableException::class);

        $client->sendRequest(new Request('GET', '/sets'));
    }

    public function testNetworkFailureIsReportedAsAnOutageAndKeepsItsCause(): void
    {
        $cause = new class('Connection timed out') extends \RuntimeException implements ClientExceptionInterface {};

        $client = new UpstreamAwareHttpClient($this->clientThrowing($cause), 'pokemon');

        try {
            $client->sendRequest(new Request('GET', '/sets'));
            self::fail('Expected an UpstreamNotAvailableException.');
        } catch (UpstreamNotAvailableException $e) {
            self::assertSame('pokemon', $e->licenceSlug);
            self::assertSame($cause, $e->getPrevious());
        }
    }
}
