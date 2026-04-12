<?php

namespace App\Service\Asterisk;

use App\Service\Asterisk\Dispatcher\AsteriskEventDispatcher;
use App\Service\Asterisk\Support\AsteriskEventNames;
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

        $interestingEvents = [
            AsteriskEventNames::DIAL_BEGIN,
            AsteriskEventNames::BRIDGE_ENTER,
            AsteriskEventNames::HANGUP,
            AsteriskEventNames::SOFT_HANGUP_REQUEST,
        ];

        if (in_array($eventName, $interestingEvents, true)) {
            $output->writeln('Event: ' . $eventName);
        }

        $this->callEventService->storeRawEvent($event);
        $this->dispatcher->dispatch($event, $output);
    }
}
