<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Import;

use App\Application\Import\ProductImportService;
use App\Domain\Writer\WriterGateway;
use App\Infrastructure\Csv\CsvReader;
use PHPUnit\Framework\TestCase;

class ItemImportServiceTest extends TestCase
{
    private string $csvFile;

    protected function setUp(): void
    {
        $this->csvFile = sys_get_temp_dir() . '/items.csv';
        file_put_contents($this->csvFile, "gtin,language,title,picture,description,price,stock\nABC123,DE,TestTitle1,pic1,DescriptionABC,9.99,100\nXYZ123,EN,TestTitle2,pic2,DescriptionXYZ,19.99,200\n");
    }

    protected function tearDown(): void
    {
        @unlink($this->csvFile);
    }

    public function testImportDispatchesRowsToWriter(): void
    {
        $writerMock = $this->createMock(WriterGateway::class);

        $captured = [];

        $writerMock->expects($this->exactly(1))
            ->method('sendBulkProducts')
            ->willReturnCallback(function ($payload) use (&$captured) {
                $captured = $payload;
            });

        $service = new ProductImportService(new CsvReader(), $writerMock);
        $service->import($this->csvFile);

        $this->assertSame([
            ['gtin' => 'ABC123', 'language' => 'DE', 'title' => 'TestTitle1', 'picture' => 'pic1', 'description' => 'DescriptionABC', 'price' => '9.99', 'stock' => '100'],
            ['gtin' => 'XYZ123', 'language' => 'EN', 'title' => 'TestTitle2', 'picture' => 'pic2', 'description' => 'DescriptionXYZ', 'price' => '19.99', 'stock' => '200'],
        ], $captured);
    }
}
