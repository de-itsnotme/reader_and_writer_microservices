<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Product;
use App\Domain\ProductRepositoryInterface;
use PDO;

class MySqlProductRepository implements ProductRepositoryInterface
{
    private \PDO $pdo;

    public function __construct(array $config)
    {
        $dns = sprintf(
            'mysql:host=%s;dbname=%s;port=%s;charset=utf8mb4',
            $config['mysql']['host'],
            $config['mysql']['database'],
            $config['mysql']['port'],
        );

        $this->pdo = new \PDO($dns, $config['mysql']['user'], $config['mysql']['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
    }

    public function save(Product $product): void
    {
        $query = $this->pdo->prepare('INSERT INTO products (gtin, language, title, picture, description, price, stock)
            VALUES (:gtin, :language, :title, :picture, :description, :price, :stock) ON DUPLICATE KEY UPDATE
            language = VALUES(language),
            title = VALUES(title),
            picture = VALUES(picture),
            description = VALUES(description),
            price = VALUES(price),
            stock = VALUES(stock)'
        );

        $query->execute([
            ':gtin' => $product->getGtin(),
            ':language' => $product->getLanguage(),
            ':title' => $product->getTitle(),
            ':picture' => $product->getPicture(),
            ':description' => $product->getDescription(),
            ':price' => $product->getPrice(),
            ':stock' => $product->getStock(),
        ]);
    }
}
