<?php

namespace App\ChainCommandBundle\Tests\Functional\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('parent:command')]
class ParentCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->write('Parent command!');

        return Command::SUCCESS;
    }
}
