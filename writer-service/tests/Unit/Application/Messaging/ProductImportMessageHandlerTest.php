<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Messaging;

use App\Application\Messaging\ProductImportMessageHandler;
use App\Application\Messaging\ProductImportMessageParser;
use App\Application\ProductService;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ProductImportMessageHandlerTest extends TestCase
{
    private ProductImportMessageParser $parser;
    private ProductService $service;
    private AMQPChannel $channel;
    private ProductImportMessageHandler $handler;

    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->parser = $this->createMock(ProductImportMessageParser::class);
        $this->service = $this->createMock(ProductService::class);
        $this->channel = $this->createMock(AMQPChannel::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->handler = new ProductImportMessageHandler(
            $this->parser,
            $this->service,
            $this->logger,
        );
    }

    public function test_it_acks_on_success(): void
    {
        $msg = new AMQPMessage('body');
        $msg->delivery_info['delivery_tag'] = 42;

        $products = ['dummy'];

        $this->parser->method('parse')->willReturn($products);
        $this->service->expects($this->once())->method('importBulk')->with($products);

        $this->channel->expects($this->once())
            ->method('basic_ack')
            ->with(42);

        $this->handler->handle($msg, $this->channel);
    }

    public function test_it_nacks_on_failure(): void
    {
        $msg = new AMQPMessage('body');
        $msg->delivery_info['delivery_tag'] = 99;

        $this->parser->method('parse')->willThrowException(new \RuntimeException());

        $this->channel->expects($this->once())
            ->method('basic_nack')
            ->with(99, false, false);

        $this->handler->handle($msg, $this->channel);
    }
}
