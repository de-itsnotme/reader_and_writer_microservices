<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\Product\ProductService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ProductController
{
    public function __construct(private readonly ProductService $productService)
    {
    }

    #[Route('/products', name: 'create_product', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return new JsonResponse(['error' => 'invalid json'], 400);
        }

        try {
            $this->productService->create($payload);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'Internal error'], 500);
        }

        return new JsonResponse(['status' => 'accepted'], 202);
    }
}
