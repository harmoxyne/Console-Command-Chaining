<?php

namespace App\ChainCommandBundle\Tests\Functional\Command;

use App\ChainCommandBundle\Attributes\ChainChildren;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('child:command')]
#[ChainChildren('parent:command')]
class ChildCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->write('Child command!');

        return Command::SUCCESS;
    }

}
