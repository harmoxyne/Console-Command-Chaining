<?php

namespace App\ChainCommandBundle\Tests\Functional\Command;

use App\ChainCommandBundle\ServiceLocator\CommandChainLocator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class CommandChainTest extends KernelTestCase
{
    protected Application $application;

    public function setUp(): void
    {
        parent::setUp();

        $kernel = self::bootKernel();

        $this->application = new Application($kernel);
        $this->application->setAutoExit(false);
    }

    public function testChildrenCommandGetsDiscovered()
    {
        $locator = $this->getContainer()->get(CommandChainLocator::class);

        $chain = $locator->getChainCommands('parent:command');
        $this->assertInstanceOf(ChildCommand::class, $chain[0]);
    }

    public function testChildrenCommandGetsExecutedOnParent(): void
    {
        $bufferedOutput = new BufferedOutput();
        $this->application->run(
            new ArrayInput(['parent:command']),
            $bufferedOutput
        );

        $this->assertEquals('Parent command!Child command!', $bufferedOutput->fetch());
    }

    public function testChildrenCommandCantBeExecutedOnItsOwn(): void
    {
        $bufferedOutput = new BufferedOutput();
        $this->application->run(
            new ArrayInput(['child:command']),
            $bufferedOutput
        );

        $this->assertGreaterThanOrEqual(1, mb_substr_count($bufferedOutput->fetch(), 'child:command command is a member of parent:command command chain'));
    }
}
