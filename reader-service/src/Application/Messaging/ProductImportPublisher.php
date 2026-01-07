<?php

declare(strict_types=1);

namespace App\Application\Messaging;

interface ProductImportPublisher
{
    public function publish(ProductImportMessage $message): void;
}
