<?php

declare(strict_types=1);

namespace App\Application\Product\Query;

readonly class ProductView
{
    public function __construct(
        public string  $gtin,
        public string  $language,
        public string  $title,
        public ?string $picture,
        public ?string $description,
        public float   $price,
        public int     $stock,
    ) {
    }
}
