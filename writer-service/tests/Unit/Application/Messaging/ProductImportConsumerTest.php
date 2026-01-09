<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Messaging;

use App\Application\Messaging\ProductImportConsumer;
use App\Application\Messaging\ProductImportMessageHandler;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;

class ProductImportConsumerTest extends TestCase
{
    private AMQPStreamConnection $connection;
    private AMQPChannel $channel;
    private ProductImportMessageHandler $handler;

    private ProductImportConsumer $consumer;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(AMQPStreamConnection::class);
        $this->channel = $this->createMock(AMQPChannel::class);
        $this->handler = $this->createMock(ProductImportMessageHandler::class);

        $this->connection
            ->method('channel')
            ->willReturn($this->channel);

        $this->consumer = new ProductImportConsumer(
            $this->connection,
            'exchange',
            'routing.key',
            'queue',
            'dlx',
            'dlq',
            $this->handler
        );
    }

    public function test_setup_infrastructure_declares_all_components(): void
    {
        $calls = [];

        $this->channel
            ->method('exchange_declare')
            ->willReturnCallback(function (...$args) use (&$calls) {
                $calls[] = $args;
            });

        // Trigger setup
        $ref = new \ReflectionClass($this->consumer);
        $method = $ref->getMethod('setupInfrastructure');
        $method->setAccessible(true);
        $method->invoke($this->consumer);

        // Assert the two calls in order
        $this->assertSame('exchange', $calls[0][0]); // first call
        $this->assertSame('dlx', $calls[1][0]);      // second call
    }

    public function test_consume_calls_handler_for_each_message(): void
    {
        $msg1 = new AMQPMessage('one');
        $msg2 = new AMQPMessage('two');

        $this->channel
            ->expects($this->exactly(2))
            ->method('basic_get')
            ->with('queue', false)
            ->willReturnOnConsecutiveCalls($msg1, $msg2);

        $calls = [];

        $this->handler
            ->expects($this->exactly(2))
            ->method('handle')
            ->willReturnCallback(function ($msg, $channel) use (&$calls) {
                $calls[] = $msg;
            });

        $this->consumer->consume(2);

        $this->assertSame([$msg1, $msg2], $calls);
    }

    public function test_consume_stops_when_no_message(): void
    {
        $this->channel
            ->expects($this->once())
            ->method('basic_get')
            ->with('queue', false)
            ->willReturn(null);

        $this->handler
            ->expects($this->never())
            ->method('handle');

        $this->consumer->consume(5);
    }

    public function test_consumeForever_registers_callback(): void
    {
        $msg = new AMQPMessage('test');

        // Simulate callback being registered
        $this->channel
            ->expects($this->once())
            ->method('basic_consume')
            ->with(
                'queue',
                '',
                false,
                false,
                false,
                false,
                $this->callback(function ($cb) use ($msg) {
                    $cb($msg);
                    return true;
                })
            );

        $this->handler
            ->expects($this->once())
            ->method('handle')
            ->with($msg, $this->channel);

        // Simulate consuming loop
        $this->channel->is_consuming = true;

        $this->channel
            ->method('wait')
            ->willReturnCallback(function () {
                // After first wait, stop consuming
                $this->channel->is_consuming = false;
            });

        $this->consumer->consumeForever();
    }
}
