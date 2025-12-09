<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Domain\Writer\WriterGateway;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class WriterHttpGateway implements WriterGateway
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string              $baseUrl,
    )
    {
    }
    public function sendItem(array $item): void
    {
        $response = $this->httpClient->request('POST', $this->baseUrl . '/item', [
            'json' => $item,
        ]);

        if ($response->getStatusCode() >= 400) {
            throw new \RuntimeException(
                'Writer-service rejected the item. HTTP status code: %d' . $response->getStatusCode()
            );
        }
    }
}
