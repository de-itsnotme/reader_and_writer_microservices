<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Mapper;

use App\Domain\Product;
use App\Infrastructure\Doctrine\Entity\DoctrineProduct;

readonly class ProductMapper
{
    public static function toDoctrine(Product $product): DoctrineProduct
    {
        return new DoctrineProduct(
            $product->getGtin(),
            $product->getLanguage(),
            $product->getTitle(),
            $product->getPicture(),
            $product->getDescription(),
            $product->getPrice(),
            $product->getStock(),
        );
    }

    public static function toDomain(DoctrineProduct $entity): Product
    {
        return new Product(
            $entity->getGtin(),
            $entity->getLanguage(),
            $entity->getTitle(),
            $entity->getPicture(),
            $entity->getDescription(),
            $entity->getPrice(),
            $entity->getStock()
        );
    }
}
