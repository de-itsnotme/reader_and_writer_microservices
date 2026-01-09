<?php

declare(strict_types=1);

namespace App\Application\Messaging;

use App\Application\ProductService;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class ProductImportMessageHandler
{
    public function __construct(
        private readonly ProductImportMessageParser $parser,
        private readonly ProductService $service
    ) {}

    public function handle(AMQPMessage $msg, AMQPChannel $channel): void
    {
        $tag = $msg->delivery_info['delivery_tag'];

        try {
            $products = $this->parser->parse($msg->body);

            $this->service->importBulk($products);
            $channel->basic_ack($tag);
        } catch (\Throwable $e) {
            $channel->basic_nack($tag, false, false);
        }
    }
}
