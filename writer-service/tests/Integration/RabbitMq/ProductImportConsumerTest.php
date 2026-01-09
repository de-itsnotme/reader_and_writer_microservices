<?php

declare(strict_types=1);

namespace App\Tests\Integration\RabbitMq;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use App\Application\Messaging\ProductImportConsumer;
use App\Application\Messaging\ProductImportMessageHandler;
use App\Application\Messaging\ProductImportMessageParser;
use App\Application\ProductService;

class ProductImportConsumerTest extends KernelTestCase
{
    private AMQPStreamConnection $connection;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->connection = new AMQPStreamConnection(
            $_ENV['RABBITMQ_HOST'],
            (int) $_ENV['RABBITMQ_PORT'],
            $_ENV['RABBITMQ_USER'],
            $_ENV['RABBITMQ_PASSWORD']
        );

        $channel = $this->connection->channel();

        // Clean queues before each test
        $channel->queue_delete($_ENV['RABBITMQ_DLQ']);
        $channel->queue_delete($_ENV['RABBITMQ_QUEUE_PRODUCT_IMPORT_WRITER']);
    }

    public function test_it_consumes_valid_message(): void
    {
        $channel = $this->connection->channel();

        // Mock ProductService
        $productService = $this->createMock(ProductService::class);
        $productService->expects($this->once())
            ->method('importBulk');

        // Mock Logger
        $logger = $this->createMock(LoggerInterface::class);

        // Real parser
        $parser = new ProductImportMessageParser();

        // Real handler with mocked service
        $handler = new ProductImportMessageHandler($parser, $productService, $logger);

        // Create consumer
        $consumer = new ProductImportConsumer(
            $this->connection,
            $_ENV['RABBITMQ_EXCHANGE'],
            $_ENV['RABBITMQ_ROUTING_KEY'],
            $_ENV['RABBITMQ_QUEUE_PRODUCT_IMPORT_WRITER'],
            $_ENV['RABBITMQ_DLX'],
            $_ENV['RABBITMQ_DLQ'],
            $handler
        );

        // 1) Force infrastructure creation
        $consumer->consume(0);

        // 2) Publish AFTER infra exists
        $payload = json_encode([
            'products' => [
                [
                    'gtin' => '123',
                    'language' => 'en',
                    'title' => 'Test',
                    'picture' => 'url',
                    'description' => 'desc',
                    'price' => 10.5,
                    'stock' => 5
                ]
            ]
        ]);

        $msg = new AMQPMessage($payload, ['content_type' => 'application/json']);

        $channel->basic_publish(
            $msg,
            $_ENV['RABBITMQ_EXCHANGE'],
            $_ENV['RABBITMQ_ROUTING_KEY']
        );

        // 3) Now consume the message
        $consumer->consume(1);

        $this->assertTrue(true);
    }

    public function test_invalid_message_goes_to_dlq(): void
    {
        $channel = $this->connection->channel();

        // Real parser
        $parser = new ProductImportMessageParser();

        // Real ProductService (autowired)
        $productService = static::getContainer()->get(ProductService::class);

        // Mock Logger
        $logger = $this->createMock(LoggerInterface::class);

        // Real handler
        $handler = new ProductImportMessageHandler($parser, $productService, $logger);

        // Create consumer
        $consumer = new ProductImportConsumer(
            $this->connection,
            $_ENV['RABBITMQ_EXCHANGE'],
            $_ENV['RABBITMQ_ROUTING_KEY'],
            $_ENV['RABBITMQ_QUEUE_PRODUCT_IMPORT_WRITER'],
            $_ENV['RABBITMQ_DLX'],
            $_ENV['RABBITMQ_DLQ'],
            $handler
        );

        // 1) Force infrastructure creation
        $consumer->consume(0);

        // 2) Publish invalid message
        $payload = json_encode(['products' => [['gtin' => '123']]]);
        $msg = new AMQPMessage($payload, ['content_type' => 'application/json']);

        $channel->basic_publish(
            $msg,
            $_ENV['RABBITMQ_EXCHANGE'],
            $_ENV['RABBITMQ_ROUTING_KEY']
        );

        // 3) Consume (this should nack → DLQ)
        $consumer->consume(1);

        // 4) Check DLQ (passive)
        list($queue, $messageCount) = $channel->queue_declare($_ENV['RABBITMQ_DLQ'], true);

        $this->assertGreaterThan(0, $messageCount);
    }
}
