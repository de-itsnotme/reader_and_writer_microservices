<?php

declare(strict_types=1);

namespace App\Application\Messaging;

class ProductImportMessage
{
    public function __construct(
        /** @var array<int, array<string,mixed>> */
        private array $products,
        private string $batchId,
        private string $source,
        private \DateTimeImmutable $importedAt,
    ) {
    }

    public function toArray(): array
    {
        return [
            'products' => $this->products,
            'batch_id' => $this->batchId,
            'source' => $this->source,
            'imported_at' => $this->importedAt->format(DATE_ATOM),
        ];
    }
}
