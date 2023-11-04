<?php

namespace App\ChainCommandBundle\Tests\Unit\ServiceLocator;

use App\ChainCommandBundle\ServiceLocator\CommandChainLocator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;

class CommandChainLocatorTest extends TestCase
{
    public function testCommandInChainAfterAdding(): void
    {
        $locator = new CommandChainLocator();

        $command = new Command('test:command');

        $locator->addChainCommand($command, 'parent:command', 0);

        $chain = $locator->getChainCommands('parent:command');

        $this->assertEquals($command, $chain[0]);
    }

    public function testGettingChainNameByChildrenCommand(): void
    {
        $locator = new CommandChainLocator();

        $command = new Command('test:command');

        $locator->addChainCommand($command, 'parent:command', 0);

        $chainName = $locator->getChainByChildren('test:command');

        $this->assertEquals('parent:command', $chainName);
    }

    public function testCommandsAreSortedBasedOnSortIndex(): void
    {
        $locator = new CommandChainLocator();

        $firstCommand = new Command('first:command');
        $secondCommand = new Command('second:command');

        $locator->addChainCommand($secondCommand, 'parent:command', 1);
        $locator->addChainCommand($firstCommand, 'parent:command', 0);

        $chain = $locator->getChainCommands('parent:command');

        $this->assertEquals($firstCommand, $chain[0]);
        $this->assertEquals($secondCommand, $chain[1]);
    }
}
