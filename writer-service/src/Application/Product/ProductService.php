<?php

declare(strict_types=1);

namespace App\Application\Product;

class ProductService
{
    public function create(array $data): void
    {
        foreach (['gtin','language','title','picture','description','price','stock'] as $fieldName) {
            if (!array_key_exists($fieldName, $data)) {
                throw new \InvalidArgumentException('Missing field: ' . $fieldName);
            }
        }
    }
}
