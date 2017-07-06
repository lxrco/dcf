<?php

namespace Lxr\Dcf\Commands;

use Lxr\Dcf\SymbolExtractor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UnusedSymbolsCommand extends Command
{
    public function configure(): void
    {
        parent::configure();

        $this->setName('unused:symbols')
            ->addOption('directory', 'd', InputOption::VALUE_REQUIRED, 'Path to a directory that will be scanned')
            ->setDescription('List all symbols from project directory');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $usedSymbols = file_get_contents('php://stdin');
        if ($usedSymbols === '') {
            throw new \InvalidArgumentException('Please pipe in used symbols file');
        }
        $usedSymbols = explode("\n", $usedSymbols);

        $unusedSymbols = [];

        $symbolExtractor = new SymbolExtractor();
        foreach ($symbolExtractor->getFileSymbols($input->getOption('directory')) as $file => $symbols) {
            $printedFilename = false;
            foreach ($symbols as $symbol) {
                if (!in_array($symbol, $usedSymbols, true)) {
                    if (!$printedFilename) {
                        $output->writeln($file . ':');
                        $printedFilename = true;
                    }
                    $output->writeln("\t" . $symbol);
                }
            }
        }
    }
}
