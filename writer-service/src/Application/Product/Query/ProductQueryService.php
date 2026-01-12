<?php

declare(strict_types=1);

namespace App\Application\Product\Query;

use App\Domain\ProductRepositoryInterface;

final class ProductQueryService implements ProductQueryServiceInterface
{
    public function __construct(private ProductRepositoryInterface $repository)
    {
    }

    public function findAll(int $limit = 50, int $offset = 0): array
    {
        return $this->repository->findAll($limit, $offset);
    }

    public function findOneByGtin(string $gtin): ?ProductView
    {
        $product = $this->repository->findByGtin($gtin);

        if (!$product) {
            return null;
        }

        return new ProductView(
            $product->getGtin(),
            $product->getLanguage(),
            $product->getTitle(),
            $product->getPicture(),
            $product->getDescription(),
            $product->getPrice(),
            $product->getStock(),
        );
    }
}
