<?php

declare(strict_types=1);

namespace App\Application\Messaging;

class TransportSelectorPublisher implements ProductImportPublisher
{
    public function __construct(
        private PublisherRegistry $registry,
        private TransportProvider $provider,
    ) {
    }

    public function publish(ProductImportMessage $message): void
    {
        $key = $this->provider->getTransportKey();
        $publisher = $this->registry->get($key);

        $publisher->publish($message);
    }
}
