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
        $data = json_decode($request->getContent(), true);

        foreach (['gtin','language','title','picture','description','price','stock'] as $key) {
            if (!isset($data[$key])) {
                return new JsonResponse(['error' => "Missing field: {$key}"], 400);
            }
        }

        $product = new Product(
            (string) $data['gtin'],
            (string) $data['language'],
            (string) $data['title'],
            (string) $data['picture'],
            (string) $data['description'],
            (float) $data['price'],
            (int) $data['stock'],
        );

        $this->productService->import($product);

        return new JsonResponse(['status' => 'ok'], 201);
    }
}
