<?php

declare(strict_types=1);

namespace App\Command;

use App\Domain\File\FileStorage;
use App\Infrastructure\Csv\CsvReader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-items',
    description: 'Import items from a CSV file',
)]
class ImportItemsCommand extends Command
{
    public function __construct(
        private readonly FileStorage $fileStorage,
        private readonly CsvReader   $csvReader,
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
        $io = new SymfonyStyle($input, $output);
        $filename = $input->getArgument('path');

        if (!$this->fileStorage->exists($filename)) {
            $io->error('File not found: ' . $filename);
        }

        $rows = $this->csvReader->read($this->fileStorage->resolve($filename));

        $io->success(sprintf('Parsed %d rows from %s', count($rows), $filename));

        return Command::SUCCESS;
    }
}
