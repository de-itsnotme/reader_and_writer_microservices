<?php

declare(strict_types=1);

namespace App\Application\Messaging;

use App\Domain\Product;
use JsonException;

class ProductImportMessageParser
{
    /** @return Product[]
     *
     * @throws JsonException
     */
    public function parse(string $json): array
    {
        $data = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

        if (!isset($data['products']) || !is_array($data['products'])) {
            throw new \RuntimeException('Invalid message: missing products array');
        }

        $products = [];

        foreach ($data['products'] as $item) {
            $required = ['gtin','language','title','picture','description','price','stock'];

            foreach ($required as $key) {
                if (!array_key_exists($key, $item)) {
                    throw new \RuntimeException("Invalid product: missing field {$key}");
                }
            }

            $products[] = new Product(
                (string) $item['gtin'],
                (string) $item['language'],
                (string) $item['title'],
                (string) $item['picture'],
                (string) $item['description'],
                (float) $item['price'],
                (int) $item['stock'],
            );
        }

        return $products;
    }
}
