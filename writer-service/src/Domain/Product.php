<?php

declare(strict_types=1);

namespace App\Domain;

final class Product
{
    public function __construct(
        private string $gtin,
        private string $language,
        private string $title,
        private string $picture,
        private string $description,
        private float $price,
        private int $stock,
    ) {
    }

    public function getGtin(): string
    {
        return $this->gtin;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getPicture(): string
    {
        return $this->picture;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getStock(): int
    {
        return $this->stock;
    }
}
