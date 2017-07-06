<?php

namespace Lxr\Dcf\Commands;

use Lxr\Dcf\SymbolExtractor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UnusedFilesCommand extends Command
{
    public function configure(): void
    {
        parent::configure();

        $this->setName('unused:files')
            ->addOption('directory', 'd', InputOption::VALUE_REQUIRED, 'Path to a directory that will be scanned')
            ->setDescription('List all un-loaded files from project directory');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $usedSymbols = file_get_contents('php://stdin');
        if ($usedSymbols === '') {
            throw new \InvalidArgumentException('Please pipe in used symbols file');
        }
        $usedSymbols = explode("\n", $usedSymbols);

        $usedFiles = [];
        $unusedFiles = [];

        $symbolExtractor = new SymbolExtractor();
        foreach ($symbolExtractor->getFileSymbols($input->getOption('directory')) as $file => $symbols) {
            $unusedFiles[] = $file;
        }

        // Removed loaded files
        foreach ($usedSymbols as $usedSymbol) {
            if (strpos($usedSymbol, 'load::') === 0) {
                $file = substr($usedSymbol, 6);
                foreach ($unusedFiles as $k => $unusedFile) {
                    if (substr($unusedFile, -strlen($file)) === $file) {
                        array_splice($unusedFiles, $k, 1);
                    }
                }
            }
        }

        echo implode("\n", $unusedFiles);
    }
}
