<?php

namespace Inside\PhpToGpc;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PhpParser\ParserFactory;
use PhpParser\Error;

class Command extends BaseCommand
{
    function configure()
    {
        $this
            ->setName('cronus-compiler')
            ->setHidden(true)
            ->getDefinition()
            ->addArguments([
                new InputArgument(
                    'input', InputArgument::REQUIRED, 'PHP source file'
                ),
                new InputArgument(
                    'output', InputArgument::OPTIONAL, 'GPC target file. Default: the same file but with .gpc ext'
                )
            ])
        ;
    }
    
    function execute(InputInterface $input, OutputInterface $output)
    {
        $phpFile = $input->getArgument('input');
        if (!file_exists($phpFile)) {
            $output->writeln(".php file not found: {$phpFile}");
            return;
        }
        
        $gpcFile = $input->getArgument('output');
        if (!$gpcFile) {
            $gpcFile = dirname($phpFile).DIRECTORY_SEPARATOR.basename($phpFile, '.php').'.gpc';
        }

        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);

        $prettyPrinter = new Printer;

        $output->writeln("Target file: {$gpcFile}");

        try {
            $nodes = $parser->parse(file_get_contents($phpFile));

            $gpcScript = $prettyPrinter->prettyPrint($nodes);
            if (!file_put_contents($gpcFile, $gpcScript)) {
                $output->writeln("Can't save .gpc script: {$gpcFile}");
            }
        } catch (Error $e) {
            $output->writeln('Parse Error: ', $e->getMessage());
        }

        $output->writeln('Finished');
    }
}