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
    public function sendProduct(array $product): void
    {
        $response = $this->httpClient->request('POST', rtrim($this->baseUrl . '/products'), [
            'json' => $product,
        ]);

        if ($response->getStatusCode() >= 400) {
            throw new \RuntimeException(
                'Writer-service rejected the product. HTTP status code: %d' . $response->getStatusCode()
            );
        }
    }
}
