<?php

declare(strict_types=1);

namespace App\Service\Licence\Magic;

use App\Service\Licence\Dto\Card;
use App\Service\Licence\Dto\Extension;
use App\Service\Licence\LicenceClientInterface;
use App\Service\Licence\LicenceNotFoundException;
use App\Service\Licence\UpstreamNotAvailableException;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @phpstan-import-type ScryfallSet from MagicNormaliser
 * @phpstan-import-type ScryfallCard from MagicNormaliser
 */
#[AutoconfigureTag('app.licence_client', ['slug' => 'magic'])]
final readonly class MagicClient implements LicenceClientInterface
{
    private const SLUG = 'magic';

    public function __construct(
        private HttpClientInterface $scryfallClient,
        private MagicNormaliser $normaliser,
    ) {
    }

    /** @return Extension[] */
    public function listExtensions(): array
    {
        $payload = $this->fetch('sets');

        /** @var list<ScryfallSet> $sets */
        $sets = $payload['data'];

        return array_map(
            $this->normaliser->normaliseExtension(...),
            $sets,
        );
    }

    /**
     * @return Card[]
     */
    public function listCards(string $extensionId): array
    {
        $path = 'cards/search?q='.urlencode("set:{$extensionId}");
        $cards = [];

        do {
            $payload = $this->fetch($path);

            /** @var list<ScryfallCard> $page */
            $page = $payload['data'];

            foreach ($page as $card) {
                $cards[] = $this->normaliser->normaliseCard($card);
            }

            $next = $payload['next_page'] ?? null;
            $path = \is_string($next) ? $next : null;
        } while (null !== $path);

        return $cards;
    }

    public function getCard(string $cardId): Card
    {
        /** @var ScryfallCard $card */
        $card = $this->fetch("cards/{$cardId}");

        return $this->normaliser->normaliseCard($card);
    }

    /**
     * Single entry point for every Scryfall call, so upstream failures are always
     * reported as outages rather than missing resources.
     *
     * @return array<array-key, mixed>
     */
    private function fetch(string $path): array
    {
        try {
            return $this->scryfallClient->request('GET', $path)->toArray();
        } catch (ClientExceptionInterface $e) {
            throw new LicenceNotFoundException("Not found upstream: {$path}", 0, $e);
        } catch (ExceptionInterface $e) {
            throw new UpstreamNotAvailableException(self::SLUG, 'Upstream Scryfall is unavailable', $e);
        }
    }
}
