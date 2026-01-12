<?php

declare(strict_types=1);

namespace App\Domain;

interface ProductRepositoryInterface
{
    public function save(Product $product): void;

    public function findByGtin(string $gtin): ?Product;

    /**
     * @return Product[]
     */
    public function findAll(int $limit, int $offset): array;
}
