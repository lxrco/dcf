<?php

namespace Lxr\Dcf\Commands;

use Lxr\Dcf\DeadCodeFinder;
use Lxr\Dcf\SymbolExtractor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SymbolsCommand extends Command
{
    public function configure(): void
    {
        parent::configure();

        $this->setName('symbols')
            ->addOption('directory', 'd', InputOption::VALUE_REQUIRED, 'Path to a directory that will be scanned')
            ->setDescription('List all symbols from project directory');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $symbolExtractor = new SymbolExtractor();
        foreach ($symbolExtractor->getFileSymbols($input->getOption('directory')) as $file => $symbols) {
            foreach ($symbols as $symbol) {
                $output->writeln($symbol);
            }
        }
    }
}
