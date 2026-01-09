<?php

declare(strict_types=1);

namespace App\Infrastructure\Command;

use App\Application\Messaging\ProductImportConsumer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:rabbitmq:consume-product-imports')]
class ConsumeProductImportsCommand extends Command
{
    public function __construct(private readonly ProductImportConsumer $consumer)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Starting RabbitMQ consumer for product imports...</info>');

        $this->consumer->consume();

        return Command::SUCCESS;
    }
}
