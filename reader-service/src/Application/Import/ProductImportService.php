<?php

declare(strict_types=1);

namespace App\Application\Import;

use App\Application\Messaging\ProductImportMessage;
use App\Application\Messaging\ProductImportPublisher;
use App\Infrastructure\Csv\CsvReader;
use DateTimeImmutable;

class ProductImportService
{
    public function __construct(
        private CsvReader $csvReader,
        private ProductImportPublisher $publisher,
    ) {
    }

    public function import(string $path): void
    {
        $products = [];

        foreach ($this->csvReader->iterate($path) as $row) {
            $products[] = [
                'gtin' => $row['gtin'],
                'language' => $row['language'],
                'title' => $row['title'],
                'picture' => $row['picture'],
                'description' => $row['description'],
                'price' => $row['price'],
                'stock' => $row['stock'],
            ];
        }

        $message = new ProductImportMessage(
            products: $products,
            batchId: uniqid('batch_', true),
            source: 'reader-service',
            importedAt: new DateTimeImmutable(),
        );

        $this->publisher->publish($message);
    }
}
