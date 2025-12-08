<?php

declare(strict_types=1);

namespace App\Domain\File;

interface FileStorage
{
    public function resolve(string $filename): string;
    public function exists(string $filename): bool;
    public function read(string $filename): string;
}
