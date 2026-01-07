<?php

declare(strict_types=1);

namespace App\Application\Messaging;

class PublisherRegistry
{
    /** @var array<string, ProductImportPublisher> */
    private array $publishers = [];

    public function add(string $key, ProductImportPublisher $publisher): void
    {
        $this->publishers[$key] = $publisher;
    }

    public function get(string $key): ProductImportPublisher
    {
        if (!isset($this->publishers[$key])) {
            throw new \RuntimeException("Unknown publisher: $key");
        }

        return $this->publishers[$key];
    }
}
