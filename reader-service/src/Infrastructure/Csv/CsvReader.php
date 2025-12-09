<?php

declare(strict_types=1);

namespace App\Infrastructure\Csv;

use Generator;
use RuntimeException;

final class CsvReader
{
    public function iterate(string $path): Generator
    {
        if (!is_readable($path)) {
            throw new RuntimeException("CSV file not found: $path");
        }

        if (($handle = fopen($path, 'r')) === false) {
            throw new RuntimeException("Cannot open CSV file: $path");
        }

        $headers = fgetcsv($handle);

        if ($headers === false) {
            fclose($handle);

            return;
        }

        while (($row = fgetcsv($handle)) !== false) {
            yield array_combine($headers, $row);
        }

        fclose($handle);
    }
}
