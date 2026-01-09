<?php

declare(strict_types=1);

namespace App\Application\Messaging;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;

class ProductImportConsumer
{
    public function __construct(
        private readonly AMQPStreamConnection $connection,
        private readonly string $exchangeName,
        private readonly string $routingKey,
        private readonly string $queueName,
        private readonly string $rabbitMqDlx,
        private readonly string $rabbitMqDlq,
        private readonly ProductImportMessageHandler $handler,
    ) {}

    /**
     * Production entry point – long running.
     */
    public function consumeForever(): void
    {
        $channel = $this->setupInfrastructure();

        $callback = function (AMQPMessage $msg) use ($channel) {
            $this->handler->handle($msg, $channel);
        };

        $channel->basic_consume(
            $this->queueName,
            '',
            false,
            false,
            false,
            false,
            $callback
        );

        while ($channel->is_consuming) {
            try {
                $channel->wait(null, false, 60);
            } catch (AMQPTimeoutException) {
                // continue waiting
            }
        }
    }

    /**
     * Test‑friendly: pull up to $maxMessages synchronously and return.
     */
    public function consume(int $maxMessages = 1): void
    {
        $channel = $this->setupInfrastructure();
        $processed = 0;

        while ($processed < $maxMessages) {
            $msg = $channel->basic_get($this->queueName, false);

            if ($msg === null) {
                break;
            }

            $this->handler->handle($msg, $channel);
            $processed++;
        }
    }

    private function setupInfrastructure()
    {
        $channel = $this->connection->channel();

        // Main exchange
        $channel->exchange_declare(
            $this->exchangeName,
            'topic',
            false,
            true,
            false
        );

        // Main queue with DLX
        $channel->queue_declare(
            $this->queueName,
            false,
            true,
            false,
            false,
            false,
            [
                'x-dead-letter-exchange' => ['S', $this->rabbitMqDlx],
            ]
        );

        $channel->queue_bind(
            $this->queueName,
            $this->exchangeName,
            $this->routingKey
        );

        // Dead Letter Exchange
        $channel->exchange_declare(
            $this->rabbitMqDlx,
            'fanout',
            false,
            true,
            false
        );

        // Dead Letter Queue
        $channel->queue_declare(
            $this->rabbitMqDlq,
            false,
            true,
            false
        );

        $channel->queue_bind(
            $this->rabbitMqDlq,
            $this->rabbitMqDlx
        );

        return $channel;
    }
}
