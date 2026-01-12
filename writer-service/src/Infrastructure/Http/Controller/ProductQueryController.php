<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller;

use App\Application\Product\Query\ProductQueryServiceInterface;
use App\Application\Product\Query\ProductView;
use App\Domain\Product;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/products', name: 'products_')]
final readonly class ProductQueryController
{
    public function __construct(
        private ProductQueryServiceInterface $queryService,
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $limit = (int) $request->query->get('limit', 50);
        $offset = (int) $request->query->get('offset', 0);

        $products = $this->queryService->findAll($limit, $offset);

        return new JsonResponse([
            'items' => array_map(fn (Product $p) => [
                'gtin' => $p->getGtin(),
                'language' => $p->getLanguage(),
                'title' => $p->getTitle(),
                'picture' => $p->getPicture(),
                'description' => $p->getDescription(),
                'price' => $p->getPrice(),
                'stock' => $p->getStock(),
            ], $products),
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    #[Route('/{gtin}', name: 'detail', methods: ['GET'])]
    public function detail(string $gtin): JsonResponse
    {
        /** @var ProductView $product */
        $product = $this->queryService->findOneByGtin($gtin);

        if (!$product) {
            return new JsonResponse(['message' => 'Product not found'], 404);
        }

        return new JsonResponse([
            'gtin' => $product->gtin,
            'language' => $product->language,
            'title' => $product->title,
            'picture' => $product->picture,
            'description' => $product->description,
            'price' => $product->price,
            'stock' => $product->stock,
        ]);
    }
}
