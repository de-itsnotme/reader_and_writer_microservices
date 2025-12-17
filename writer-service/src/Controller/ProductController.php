<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\ProductService;
use App\Domain\Product;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class ProductController
{
    public function __construct(private ProductService $productService)
    {
    }

    #[Route('/products', name: 'create_product', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $productsData = json_decode($request->getContent(), true);

        if (!isset($productsData[0]) || !is_array($productsData[0])) {
            return new JsonResponse(['error' => 'The data must must contain one or more rows of Product'], 400);
        }

        $processed = 0;

        foreach ($productsData as $item) {
            foreach (['gtin','language','title','picture','description','price','stock'] as $key) {
                if (!isset($item[$key])) {
                    return new JsonResponse(['error' => "Missing field: {$key}"], 400);
                }
            }

            $product = new Product(
                (string) $item['gtin'],
                (string) $item['language'],
                (string) $item['title'],
                (string) $item['picture'],
                (string) $item['description'],
                (float) $item['price'],
                (int) $item['stock'],
            );

            $this->productService->import($product);
            $processed++;
        }

        return new JsonResponse(
            [
            'status' => 'ok',
            'processed' => $processed,
            ],
            201
        );
    }
}
