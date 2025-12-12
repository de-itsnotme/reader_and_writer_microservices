<?php

declare(strict_types=1);

namespace App\Infrastructure\Factory;

use App\Domain\ProductRepositoryInterface;
use App\Infrastructure\Persistence\MySqlProductRepository;
use InvalidArgumentException;

final class StorageFactory
{
    public static function create(array $config): ProductRepositoryInterface
    {
        $type = $config['type'] ?? 'mysql';

        return match ($type) {
            'mysql' => new MySqlProductRepository($config),
            //'json' => new JsonProductRepository($config),
            //'xml' => new XmlProductRepository($config),
            default => throw new InvalidArgumentException(sprintf('Type "%s" is not supported.', $type)),
        };
    }
}
