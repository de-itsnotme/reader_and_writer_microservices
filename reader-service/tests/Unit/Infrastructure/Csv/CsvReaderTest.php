<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Csv;

use App\Infrastructure\Csv\CsvReader;
use PHPUnit\Framework\TestCase;

final class CsvReaderTest extends TestCase
{
    private string $csvFile;

    protected function setUp(): void
    {
        $this->csvFile = sys_get_temp_dir() . '/test.csv';

        file_put_contents($this->csvFile, "title,name,price\nABC123,Demo,9.99\nXYZ789,Another,19.99\n");
    }

    protected function tearDown(): void
    {
        @unlink($this->csvFile);
    }

    public function testIterateYieldsRows(): void
    {
        $reader = new CsvReader();
        $rows = iterator_to_array($reader->iterate($this->csvFile));

        $this->assertCount(2, $rows);
        $this->assertSame(['title' => 'ABC123', 'name' => 'Demo', 'price' => '9.99'], $rows[0]);
        $this->assertSame(['title' => 'XYZ789', 'name' => 'Another', 'price' => '19.99'], $rows[1]);
    }

    public function testThrowsExceptionForMissingFile(): void
    {
        $this->expectException(\RuntimeException::class);

        new CsvReader()->iterate('/non/existent/file.csv')->current();
    }
}


