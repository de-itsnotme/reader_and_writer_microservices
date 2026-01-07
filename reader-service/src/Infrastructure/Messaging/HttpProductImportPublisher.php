<?php

declare(strict_types=1);

namespace App\Infrastructure\Messaging;

use App\Application\Messaging\ProductImportMessage;
use App\Application\Messaging\ProductImportPublisher;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HttpProductImportPublisher implements ProductImportPublisher
{
    public function __construct(
        private HttpClientInterface $client,
        private string $writerBaseUri,
    ) {
    }

    public function publish(ProductImportMessage $message): void
    {
        $this->client->request(
            'POST',
            $this->writerBaseUri . '/products',
            [
                'json' => [
                    'products' => $message->products(),
                    'batch_id' => $message->batchId(),
                    'source' => $message->source(),
                    'imported_at' => $message->importedAt()->format(DATE_ATOM),
                ],
            ]
        );
    }
}
