<?php

declare(strict_types=1);

namespace App\Application\Messaging;

use App\Application\ProductService;
use App\Domain\Product;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class ProductImportConsumer
{
    public function __construct(
        private readonly AMQPStreamConnection $connection,
        private readonly string               $exchangeName,
        private readonly string               $routingKey,
        private readonly string               $queueName,
        private readonly string               $rabbitMqDlx,
        private readonly string               $rabbitMqDlq,
        private readonly ProductService       $productService,
    ) {
    }

    public function consume(): void
    {
        $channel = $this->connection->channel();

        $channel->exchange_declare($this->exchangeName, 'topic', false, true, false);
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
        $channel->queue_bind($this->queueName, $this->exchangeName, $this->routingKey);

        // 1. Declare the Dead Letter Exchange
        $channel->exchange_declare($this->rabbitMqDlx, 'fanout', false, true, false);

        // 2. Declare the Dead Letter Queue
        $channel->queue_declare($this->rabbitMqDlq, false, true, false, false);

        // 3. Bind DLQ to DLX
        $channel->queue_bind($this->rabbitMqDlq, $this->rabbitMqDlx);

        $callback = function (AMQPMessage $msg): void {
            try {
                $payload = $msg->body;
                $data = json_decode($payload, true);

                if (
                    !isset($data['products'])
                    || !is_array($data['products'])
                ) {
                    echo 'Invalid message format: missing "products" array\n';
                }

                $products = [];

                foreach ($data['products'] as $item) {
                    foreach (['gtin','language','title','picture','description','price','stock'] as $key) {
                        if (!isset($item[$key])) {
                            echo "Invalid product: missing field {$key}\n";

                            return;
                        }
                    }

                    $products[] = new Product(
                        (string) $item['gtin'],
                        (string) $item['language'],
                        (string) $item['title'],
                        (string) $item['picture'],
                        (string) $item['description'],
                        (float) $item['price'],
                        (int) $item['stock'],
                    );
                }

                $this->productService->importBulk($products);

                printf(
                    "Imported %d products via AMQP (Timestamp: %s)\n",
                    count($products),
                    date('Y-m-d H:i:s', time())
                );

                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
            } catch (\Throwable $e) {
                echo 'Error processing message: ' . $e->getMessage() . "\n";

                // NACK + requeue
                $msg->delivery_info['channel']->basic_nack($msg->delivery_info['delivery_tag'], false, true);
            }
        };

        $channel->basic_consume(
            $this->queueName,
            '',
            false,
            false, // manual ack
            false,
            false,
            $callback
        );

        while (true) {
            try {
                $channel->wait(null, false, 60);
            } catch (\PhpAmqpLib\Exception\AMQPTimeoutException $e) {
                // No messages in 60 seconds — continue waiting
                continue;
            } catch (\Exception $e) {
                echo "Consumer stopped due to error: " . $e->getMessage() . "\n";
                break;
            }
        }
    }
}
