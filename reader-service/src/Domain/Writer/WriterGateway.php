<?php

declare(strict_types=1);

namespace App\Domain\Writer;

interface WriterGateway
{
    public function sendProduct(array $product): void;
}
