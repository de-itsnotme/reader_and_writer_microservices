<?php

declare(strict_types=1);

namespace App\Application\Product\Query;

interface ProductQueryServiceInterface
{
    /**
     * @return ProductView[]
     */
    public function findAll(int $limit = 50, int $offset = 0): array;

    public function findOneByGtin(string $gtin): ?ProductView;
}
