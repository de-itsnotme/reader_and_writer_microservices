<?php

declare(strict_types=1);

namespace App\Application\Import;

use App\Domain\Writer\WriterGateway;
use App\Infrastructure\Csv\CsvReader;

final class ProductImportService
{
    public function __construct(
        private CsvReader $csvReader,
        private WriterGateway $writer,
    )
    {
    }

    public function import(string $path): void
    {
        $payloads = [];

        foreach ($this->csvReader->iterate($path) as $row) {
            $payloads[] = [
                'gtin' => $row['gtin'],
                'language' => $row['language'],
                'title' => $row['title'],
                'picture' => $row['picture'],
                'description' => $row['description'],
                'price' => $row['price'],
                'stock' => $row['stock'],
            ];
        }

        $this->writer->sendBulk($payloads);
    }
}
