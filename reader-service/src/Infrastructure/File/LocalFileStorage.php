<?php

declare(strict_types=1);

namespace App\Infrastructure\File;

use App\Domain\File\FileStorage;

class LocalFileStorage implements FileStorage
{
    private string $baseDir;

    public function __construct(string $baseDir)
    {
        $this->baseDir = $baseDir;
    }
    public function resolve(string $filename): string
    {
        return $this->baseDir . DIRECTORY_SEPARATOR . $filename;
    }

    public function exists(string $filename): bool
    {
        return is_file($this->resolve($filename));
    }

    public function read(string $filename): string
    {
        return file_get_contents($this->resolve($filename));
    }
}
