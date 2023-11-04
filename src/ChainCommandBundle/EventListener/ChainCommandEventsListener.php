<?php

namespace App\ChainCommandBundle\EventListener;

use App\ChainCommandBundle\Events\AfterChainExecutionEvent;
use App\ChainCommandBundle\Events\BeforeChainExecutionEvent;
use App\ChainCommandBundle\Events\BeforeChildrenCommandExecutionEvent;
use App\ChainCommandBundle\Events\BeforeMasterCommandExecutionEvent;
use App\ChainCommandBundle\Events\ChainChildrenDetectedEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

readonly class ChainCommandEventsListener implements EventSubscriberInterface
{

    public function __construct(private LoggerInterface $logger)
    {

    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeChainExecutionEvent::class => 'beforeChainExecution',
            AfterChainExecutionEvent::class => 'afterChainExecution',
            ChainChildrenDetectedEvent::class => 'chainChildrenDetected',
            BeforeMasterCommandExecutionEvent::class => 'beforeMasterCommandExecution',
            BeforeChildrenCommandExecutionEvent::class => 'beforeChildrenCommandExecution',
        ];
    }

    public function beforeChainExecution(BeforeChainExecutionEvent $event): void
    {
        $this->logger->info(sprintf('%s is a master command of a command chain that has registered member commands', $event->getCommand()->getName()));
    }

    public function afterChainExecution(AfterChainExecutionEvent $event): void
    {
        $this->logger->info(sprintf('Execution of %s chain completed.', $event->getCommand()->getName()));
    }

    public function chainChildrenDetected(ChainChildrenDetectedEvent $event): void
    {
        $this->logger->info(sprintf('%s registered as a member of %s command chain', $event->getChildren()->getName(), $event->getCommand()->getName()));
    }

    public function beforeMasterCommandExecution(BeforeMasterCommandExecutionEvent $event): void
    {
        $this->logger->info(sprintf('Executing %s command itself first', $event->getCommand()->getName()));
    }

    public function beforeChildrenCommandExecution(BeforeChildrenCommandExecutionEvent $event): void
    {
        $this->logger->info(sprintf('Executing %s chain member %s', $event->getParent()->getName(), $event->getCommand()->getName()));
    }

}
