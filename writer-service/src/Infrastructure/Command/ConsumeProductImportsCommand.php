<?php

declare(strict_types=1);

namespace App\Infrastructure\Command;

use App\Application\Messaging\ProductImportConsumer;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:rabbitmq:consume-product-imports')]
class ConsumeProductImportsCommand extends Command
{
    public function __construct(
        private readonly ProductImportConsumer $consumer,
        private readonly LoggerInterface $logger,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $startingMessage = 'Starting RabbitMQ consumer for product imports...';

        $output->writeln('<info>' . $startingMessage . '</info>');
        $this->logger->info($startingMessage);
        $this->consumer->consumeForever();

        return Command::SUCCESS;
    }
}
