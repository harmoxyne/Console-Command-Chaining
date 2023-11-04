<?php

namespace App\BarBundle\Command;

use App\ChainCommandBundle\Attributes\ChainChildren;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('bar:hi')]
#[ChainChildren(parentCommand:'foo:hello', sortIndex: 0)]
class BarHiCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Hi from Bar!');

        return Command::SUCCESS;
    }
}
