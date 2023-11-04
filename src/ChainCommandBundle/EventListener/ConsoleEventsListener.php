<?php

namespace App\ChainCommandBundle\EventListener;

use App\ChainCommandBundle\Events\AfterChainExecutionEvent;
use App\ChainCommandBundle\Events\AfterChildrenCommandExecutionEvent;
use App\ChainCommandBundle\Events\AfterMasterCommandExecutionEvent;
use App\ChainCommandBundle\Events\BeforeChainExecutionEvent;
use App\ChainCommandBundle\Events\BeforeChildrenCommandExecutionEvent;
use App\ChainCommandBundle\Events\BeforeMasterCommandExecutionEvent;
use App\ChainCommandBundle\Events\ChainChildrenDetectedEvent;
use App\ChainCommandBundle\Exceptions\ChainCommandException;
use App\ChainCommandBundle\ServiceLocator\CommandChainLocator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

readonly class ConsoleEventsListener implements EventSubscriberInterface
{
    public function __construct(
        private CommandChainLocator      $chainLocator,
        private EventDispatcherInterface $eventDispatcher
    )
    {

    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleEvents::COMMAND => 'onCommand',
            ConsoleEvents::TERMINATE => 'onTerminate'
        ];
    }

    public function onCommand(ConsoleCommandEvent $commandEvent): void
    {
        $command = $commandEvent->getCommand();
        if (!$command) {
            return;
        }

        $parentChain = $this->chainLocator->getChainByChildren($command->getName());
        if ($parentChain) {
            $commandEvent->stopPropagation();
            $commandEvent->disableCommand();

            throw new ChainCommandException(sprintf(
                'Error: %s command is a member of %s command chain and cannot be executed on its own.',
                $command->getName(),
                $parentChain
            ));
        }

        $chain = $this->chainLocator->getChainCommands($command->getName());

        if (!$chain) {
            return;
        }

        $this->eventDispatcher->dispatch(new BeforeChainExecutionEvent($command, $commandEvent->getInput(), $commandEvent->getOutput()));

        foreach ($chain as $childrenCommand) {
            $this->eventDispatcher->dispatch(new ChainChildrenDetectedEvent($command, $childrenCommand, $commandEvent->getInput(), $commandEvent->getOutput()));
        }

        $this->eventDispatcher->dispatch(new BeforeMasterCommandExecutionEvent($command, $commandEvent->getInput(), $commandEvent->getOutput()));
    }

    /**
     * @throws ExceptionInterface - in case if any of the chain command fails with exception
     */
    public function onTerminate(ConsoleTerminateEvent $terminateEvent): void
    {
        $command = $terminateEvent->getCommand();
        if (!$command) {
            return;
        }

        $chain = $this->chainLocator->getChainCommands($command->getName());
        if (!$chain) {
            return;
        }

        $this->eventDispatcher->dispatch(new AfterMasterCommandExecutionEvent($command, $terminateEvent->getInput(), $terminateEvent->getOutput()));

        $input = $terminateEvent->getInput();
        $output = $terminateEvent->getOutput();

        /*
         *  In order to pass input to the children commands we need to remove parent command name from it
         */
        $correctedInput = new ArgvInput(array_slice($input->getArguments(), 1));

        array_walk($chain, function (Command $childrenCommand) use ($command, $correctedInput, $output) {
            $this->eventDispatcher->dispatch(new BeforeChildrenCommandExecutionEvent($childrenCommand, $command, $correctedInput, $output));

            $childrenCommand->run($correctedInput, $output);

            $this->eventDispatcher->dispatch(new AfterChildrenCommandExecutionEvent($childrenCommand, $correctedInput, $output));
        });

        $this->eventDispatcher->dispatch(new AfterChainExecutionEvent($command, $terminateEvent->getInput(), $terminateEvent->getOutput()));
    }
}
