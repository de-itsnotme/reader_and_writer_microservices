<?php

declare(strict_types=1);

namespace App\Domain;

interface ProductRepositoryInterface
{
    public function save(Product $product): void;
}
