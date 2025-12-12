<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Product;
use App\Domain\ProductRepositoryInterface;

final class ProductService
{
    public function __construct(private ProductRepositoryInterface $repository)
    {
    }

    public function import(Product $product): void
    {
        $this->repository->save($product);
    }
}
