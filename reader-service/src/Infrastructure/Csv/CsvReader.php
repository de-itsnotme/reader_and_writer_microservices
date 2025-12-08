<?php

declare(strict_types=1);

namespace App\Infrastructure\Csv;

class CsvReader
{
    /**
     * @param string $path Path to CSV file
     *
     * @return array<int, array<string, string>> Parsed rows as associative arrays
     */
    public function read(string $path): array
    {
        $rows = [];
        if (($handle = fopen($path, 'r')) !== false) {
            $headers = fgetcsv($handle, 1000, ',');

            if ($headers === false) {
                fclose($handle);

                return $rows;
            }

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $rows[] = array_combine($headers, $data);
            }

            fclose($handle);
        }

        return $rows;
    }


}
