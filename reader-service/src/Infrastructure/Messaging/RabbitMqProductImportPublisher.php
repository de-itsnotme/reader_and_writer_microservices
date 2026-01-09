<?php

declare(strict_types=1);

namespace App\Infrastructure\Messaging;

use App\Application\Messaging\ProductImportMessage;
use App\Application\Messaging\ProductImportPublisher;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

readonly class RabbitMqProductImportPublisher implements ProductImportPublisher
{
    public function __construct(
        private AMQPStreamConnection $connection,
        private string               $exchangeName,
        private string               $routingKey,
    ) {
    }

    public function publish(ProductImportMessage $message): void
    {
        $channel = $this->connection->channel();

        $channel->exchange_declare(
            $this->exchangeName,
            'topic',
            false,
            true,
            false
        );

        $payload = json_encode($message->toArray(), JSON_THROW_ON_ERROR);
        $amqpMessage = new AMQPMessage(
            $payload,
            ['content_type' => 'application/json']
        );

        $channel->basic_publish(
            $amqpMessage,
            $this->exchangeName,
            $this->routingKey
        );

        $channel->close();
        $this->connection->close();
    }
}
