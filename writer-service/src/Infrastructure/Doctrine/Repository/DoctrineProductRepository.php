<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Product;
use App\Domain\ProductRepositoryInterface;
use App\Infrastructure\Doctrine\Entity\DoctrineProduct;
use App\Infrastructure\Doctrine\Mapper\ProductMapper;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineProductRepository implements ProductRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function save(Product $product): void
    {
        $entity = $this->em->find(DoctrineProduct::class, $product->getGtin());

        if ($entity) {
            // Update existing entity
            $entity->setLanguage($product->getLanguage());
            $entity->setTitle($product->getTitle());
            $entity->setPicture($product->getPicture());
            $entity->setDescription($product->getDescription());
            $entity->setPrice($product->getPrice());
            $entity->setStock($product->getStock());
        } else {
            // Insert new entity
            $entity = ProductMapper::toDoctrine($product);
            $this->em->persist($entity);
        }

        $this->em->flush();
    }

    public function findByGtin(string $gtin): ?Product
    {
        $entity = $this->em->find(DoctrineProduct::class, $gtin);

        return $entity ? ProductMapper::toDomain($entity) : null;
    }

    public function findAll(int $limit, int $offset): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('p')
            ->from(DoctrineProduct::class, 'p')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        $entities = $qb->getQuery()->getResult();

        return array_map(
            fn (DoctrineProduct $entity) => ProductMapper::toDomain($entity),
            $entities
        );
    }
}
