<?php

declare(strict_types=1);

namespace App\Command;

use App\Application\Import\ProductImportService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:import-products',
    description: 'Import items from a CSV file',
)]
final class ImportItemsCommand extends Command
{
    public function __construct(
        private ProductImportService $itemImportService,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('path', InputArgument::REQUIRED, 'CSV file path (relative to project or absolute)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = (string) $input->getArgument('path');
        $this->itemImportService->import($path);

        $output->writeln('<info>Import dispatched to writer-service.</info>');

        return Command::SUCCESS;
    }
}
