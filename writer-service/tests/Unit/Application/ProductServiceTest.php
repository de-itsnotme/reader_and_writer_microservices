<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application;

use App\Application\ProductService;
use App\Domain\Product;
use App\Domain\ProductRepositoryInterface;
use PHPUnit\Framework\TestCase;

class ProductServiceTest extends TestCase
{
    private ProductRepositoryInterface $repository;
    private ProductService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ProductRepositoryInterface::class);
        $this->service = new ProductService($this->repository);
    }

    public function test_import_bulk_saves_products(): void
    {
        $products = [
            new Product('123', 'en', 'Test', 'url', 'desc', 10.5, 5),
            new Product('456', 'en', 'Another', 'url2', 'desc2', 20.0, 10),
        ];

        $this->repository
            ->expects($this->exactly(2))
            ->method('save')
            ->willReturnCallback(function ($product) use (&$calls) {
                $calls[] = $product;
            });

        $this->service->importBulk($products);

        $this->assertSame($products[0], $calls[0]);
        $this->assertSame($products[1], $calls[1]);
    }
}
