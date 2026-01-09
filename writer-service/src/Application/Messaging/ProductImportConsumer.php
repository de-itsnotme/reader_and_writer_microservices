<?php

declare(strict_types=1);

namespace App\Application\Messaging;

use App\Application\ProductService;
use App\Domain\Product;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;

class ProductImportConsumer
{
    public function __construct(
        private AMQPStreamConnection $connection,
        private string $exchangeName,
        private string $routingKey,
        private string $queueName,
        private ProductService $productService,
    ) {
    }

    public function consume(): void
    {
        $channel = $this->connection->channel();

        $channel->exchange_declare($this->exchangeName, 'topic', false, true, false);
        $channel->queue_declare($this->queueName, false, true, false, false);
        $channel->queue_bind($this->queueName, $this->exchangeName, $this->routingKey);

        $callback = function (AMQPMessage $msg): void {
            $payload = $msg->body;

            $data = json_decode($payload, true);

            if (!isset($data['products']) || !is_array($data['products'])
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
                time()
            );
        };

        $channel->basic_consume(
            $this->queueName,
            '',
            false,
            false,   // auto-ack for now; later we might do manual acks
            false,
            false,
            $callback
        );

        while (true) {
            try {
                $channel->wait(null, false, 60);
            } catch (AMQPTimeoutException $e) {
                // No messages in 60 seconds — continue waiting
                continue;
            }
        }
    }
}
