<?php

declare(strict_types=1);

namespace App\Infrastructure\Messaging;

use App\Application\Messaging\TransportProvider;

class EnvTransportProvider implements TransportProvider
{
    public function __construct(private string $transportKey)
    {
    }

    public function getTransportKey(): string
    {
        return $this->transportKey;
    }
}
