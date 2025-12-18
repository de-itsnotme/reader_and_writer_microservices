<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Product;
use App\Domain\ProductRepositoryInterface;

final readonly class ProductService
{
    public function __construct(private ProductRepositoryInterface $repository)
    {
    }

    /**
     * @param array<int, Product> $products
     *
     * @return void
     */
    public function importBulk(array $products): void
    {
        foreach ($products as $product) {
            $this->repository->save($product);
        }
    }
}
