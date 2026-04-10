<?php

namespace App\Service\Asterisk;

use App\Service\Asterisk\Dispatcher\AsteriskEventDispatcher;
use PAMI\Message\Event\EventMessage;
use Symfony\Component\Console\Output\OutputInterface;

class AsteriskEventHandler
{
    public function __construct(
        private readonly CallEventService $callEventService,
        private readonly AsteriskEventDispatcher $dispatcher,
    ) {
    }

    public function handle(EventMessage $event, OutputInterface $output): void
    {
        $eventName = $event->getName();

        $output->writeln('Event: ' . $eventName);

        $this->callEventService->storeRawEvent($event);
        $this->dispatcher->dispatch($event, $output);
    }
}
