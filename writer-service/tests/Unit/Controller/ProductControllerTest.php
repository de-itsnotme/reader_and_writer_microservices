<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller;

use App\Application\ProductService;
use App\Controller\ProductController;
use App\Domain\Product;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class ProductControllerTest extends TestCase
{
    public function testCreateBulkProducts(): void
    {
        $serviceMock = $this->createMock(ProductService::class);

        $serviceMock->expects($this->once())
            ->method('importBulk')
            ->with($this->callback(function ($products) {
                return count($products) === 2
                    && $products[0] instanceof Product
                    && $products[1] instanceof Product;
            }));

        $controller = new ProductController($serviceMock);

        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [],
            json_encode([
                'products' => [
                    [
                        'gtin' => 'ABC123',
                        'language' => 'DE',
                        'title' => 'TestTitle1',
                        'picture' => 'pic1',
                        'description' => 'DescriptionABC',
                        'price' => 9.99,
                        'stock' => 100
                    ],
                    [
                        'gtin' => 'XYZ123',
                        'language' => 'EN',
                        'title' => 'TestTitle2',
                        'picture' => 'pic2',
                        'description' => 'DescriptionXYZ',
                        'price' => 19.99,
                        'stock' => 200
                    ]
                ]
            ])
        );

        $response = $controller->create($request);

        $this->assertSame(201, $response->getStatusCode());
    }
}
