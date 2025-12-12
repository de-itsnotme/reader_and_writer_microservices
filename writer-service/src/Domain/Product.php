<?php

declare(strict_types=1);

namespace App\Domain;

final class Product
{
    private string $gtin;
    private string $language;
    private string $title;
    private string $picture;
    private string $description;
    private float $price;
    private int $stock;

    public function __construct(
        string $gtin,
        string $language,
        string $title,
        string $picture,
        string $description,
        float $price,
        int $stock,
    ) {
        $this->gtin = $gtin;
        $this->language = $language;
        $this->title = $title;
        $this->picture = $picture;
        $this->description = $description;
        $this->price = $price;
        $this->stock = $stock;
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
