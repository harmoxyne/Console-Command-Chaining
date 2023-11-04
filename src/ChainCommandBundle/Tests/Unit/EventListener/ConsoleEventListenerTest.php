<?php

namespace App\ChainCommandBundle\Tests\Unit\EventListener;

use App\ChainCommandBundle\EventListener\ConsoleEventsListener;
use App\ChainCommandBundle\Events\AfterChildrenCommandExecutionEvent;
use App\ChainCommandBundle\Events\AfterMasterCommandExecutionEvent;
use App\ChainCommandBundle\Events\BeforeChainExecutionEvent;
use App\ChainCommandBundle\Events\BeforeChildrenCommandExecutionEvent;
use App\ChainCommandBundle\Events\BeforeMasterCommandExecutionEvent;
use App\ChainCommandBundle\Events\ChainChildrenDetectedEvent;
use App\ChainCommandBundle\Exceptions\ChainCommandException;
use App\ChainCommandBundle\ServiceLocator\CommandChainLocator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ConsoleEventListenerTest extends TestCase
{

    public function testExceptionThrownIfChildrenCommandExecutesOnItsOwn(): void
    {
        $this->expectException(ChainCommandException::class);

        $commandName = 'test:command';

        $commandChainLocatorMock = $this->getCommandChainLocatorMock();
        $commandChainLocatorMock
            ->expects($this->once())
            ->method('getChainByChildren')
            ->with($commandName)
            ->willReturn('parent:command');

        $consoleEventListener = new ConsoleEventsListener(
            $commandChainLocatorMock,
            $this->getEventDispatcherMock()
        );

        $consoleCommandEvent = new ConsoleCommandEvent(
            new Command($commandName),
            new ArrayInput([]),
            new ConsoleOutput()
        );

        $consoleEventListener->onCommand($consoleCommandEvent);
    }

    public function testNoEventsSentIfCommandIsNotInChainOnCommandExecution(): void
    {
        $commandName = 'test:command';

        $commandChainLocatorMock = $this->getCommandChainLocatorMock();
        $commandChainLocatorMock
            ->expects($this->once())
            ->method('getChainCommands')
            ->with($commandName)
            ->willReturn([]);

        $eventDispatcherMock = $this->getEventDispatcherMock();
        $eventDispatcherMock->expects($this->never())
            ->method('dispatch');

        $consoleEventListener = new ConsoleEventsListener(
            $commandChainLocatorMock,
            $eventDispatcherMock
        );

        $consoleCommandEvent = new ConsoleCommandEvent(
            new Command($commandName),
            new ArrayInput([]),
            new ConsoleOutput()
        );

        $consoleEventListener->onCommand($consoleCommandEvent);
    }

    public function testNoEventsSentIfCommandIsNotInChainOnCommandTermination(): void
    {
        $commandName = 'test:command';

        $commandChainLocatorMock = $this->getCommandChainLocatorMock();
        $commandChainLocatorMock
            ->expects($this->once())
            ->method('getChainCommands')
            ->with($commandName)
            ->willReturn([]);

        $eventDispatcherMock = $this->getEventDispatcherMock();
        $eventDispatcherMock->expects($this->never())
            ->method('dispatch');

        $consoleEventListener = new ConsoleEventsListener(
            $commandChainLocatorMock,
            $eventDispatcherMock
        );

        $consoleCommandEvent = new ConsoleTerminateEvent(
            new Command($commandName),
            new ArrayInput([]),
            new ConsoleOutput(),
            0
        );

        $consoleEventListener->onTerminate($consoleCommandEvent);
    }

    /**
     * @dataProvider eventsOnCommandDataProvider
     */
    public function testEventsOnCommandAreSent(string $eventClass): void
    {
        $commandName = 'test:command';

        $childrenCommand = new Command('parent:command');

        $commandChainLocatorMock = $this->getCommandChainLocatorMock();
        $commandChainLocatorMock
            ->expects($this->once())
            ->method('getChainCommands')
            ->with($commandName)
            ->willReturn([$childrenCommand]);


        $eventFound = false;

        $eventDispatcherMock = $this->getEventDispatcherMock();
        $eventDispatcherMock->expects($this->atLeastOnce())
            ->method('dispatch')
            ->will($this->returnCallback(function ($event) use (&$eventFound, $eventClass) {
                if (!$eventFound && $event instanceof $eventClass) {
                    $eventFound = true;
                }
                return $event;
            }));
        $consoleEventListener = new ConsoleEventsListener(
            $commandChainLocatorMock,
            $eventDispatcherMock
        );

        $consoleCommandEvent = new ConsoleCommandEvent(
            new Command($commandName),
            new ArrayInput([]),
            new ConsoleOutput()
        );

        $consoleEventListener->onCommand($consoleCommandEvent);

        $this->assertTrue($eventFound, sprintf('Event %s was not dispatched onCommand', $eventClass));
    }

    public function eventsOnCommandDataProvider(): array
    {
        return [
            'BeforeChainExecutionEvent' => [BeforeChainExecutionEvent::class],
            'ChainChildrenDetectedEvent' => [ChainChildrenDetectedEvent::class],
            'BeforeMasterCommandExecutionEvent' => [BeforeMasterCommandExecutionEvent::class],
        ];
    }


    /**
     * @dataProvider eventsOnTerminateDataProvider
     */
    public function testEventsOnTerminateAreSent(string $eventClass): void
    {
        $commandName = 'test:command';

        $childrenCommand = $this->createMock(Command::class);

        $commandChainLocatorMock = $this->getCommandChainLocatorMock();
        $commandChainLocatorMock
            ->expects($this->once())
            ->method('getChainCommands')
            ->with($commandName)
            ->willReturn([$childrenCommand]);


        $eventFound = false;

        $eventDispatcherMock = $this->getEventDispatcherMock();
        $eventDispatcherMock->expects($this->atLeastOnce())
            ->method('dispatch')
            ->will($this->returnCallback(function ($event) use (&$eventFound, $eventClass) {
                if (!$eventFound && $event instanceof $eventClass) {
                    $eventFound = true;
                }
                return $event;
            }));
        $consoleEventListener = new ConsoleEventsListener(
            $commandChainLocatorMock,
            $eventDispatcherMock
        );

        $consoleCommandEvent = new ConsoleTerminateEvent(
            new Command($commandName),
            new ArrayInput([]),
            new ConsoleOutput(),
            0
        );

        $consoleEventListener->onTerminate($consoleCommandEvent);

        $this->assertTrue($eventFound, sprintf('Event %s was not dispatched onTerminate', $eventClass));
    }

    public function eventsOnTerminateDataProvider(): array
    {
        return [
            'AfterMasterCommandExecutionEvent' => [AfterMasterCommandExecutionEvent::class],
            'BeforeChildrenCommandExecutionEvent' => [BeforeChildrenCommandExecutionEvent::class],
            'AfterChildrenCommandExecutionEvent' => [AfterChildrenCommandExecutionEvent::class],
        ];
    }


    public function testChildrenCommandGetsExecuted(): void
    {
        $commandName = 'test:command';

        $childrenCommand = $this->createMock(Command::class);
        $childrenCommand->expects($this->once())
            ->method('run');

        $commandChainLocatorMock = $this->getCommandChainLocatorMock();
        $commandChainLocatorMock
            ->expects($this->once())
            ->method('getChainCommands')
            ->with($commandName)
            ->willReturn([$childrenCommand]);

        $consoleEventListener = new ConsoleEventsListener(
            $commandChainLocatorMock,
            $this->getEventDispatcherMock()
        );

        $consoleCommandEvent = new ConsoleTerminateEvent(
            new Command($commandName),
            new ArrayInput([]),
            new ConsoleOutput(),
            0
        );

        $consoleEventListener->onTerminate($consoleCommandEvent);
    }

    private function getEventDispatcherMock(): EventDispatcherInterface|MockObject
    {
        return $this->getMockForAbstractClass(EventDispatcherInterface::class);
    }

    private function getCommandChainLocatorMock(): CommandChainLocator|MockObject
    {
        return $this->createMock(CommandChainLocator::class);
    }
}
