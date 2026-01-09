<?php

declare(strict_types=1);

namespace App\Application\Messaging;

use App\Application\ProductService;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class ProductImportMessageHandler
{
    public function __construct(
        private readonly ProductImportMessageParser $parser,
        private readonly ProductService $service,
        private readonly LoggerInterface $logger,
    ) {}

    public function handle(AMQPMessage $msg, AMQPChannel $channel): void
    {
        $tag = $msg->delivery_info['delivery_tag'];

        try {
            $this->logger->info('Received message');

            $products = $this->parser->parse($msg->body);

            $this->service->importBulk($products);

            $channel->basic_ack($tag);

            $this->logger->info('Message processed successfully', [
                'count' => count($products),
            ]);
        } catch (\Throwable $e) {
            $channel->basic_nack($tag, false, false);

            $this->logger->error('Message failed', [
                'error' => $e->getMessage(),
                'body' => $msg->body,
            ]);
        }
    }
}
