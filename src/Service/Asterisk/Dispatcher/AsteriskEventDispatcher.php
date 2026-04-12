<?php

namespace App\Service\Asterisk\Dispatcher;

use App\Service\Asterisk\Handler\EventHandlerInterface;
use PAMI\Message\Event\EventMessage;
use Symfony\Component\Console\Output\OutputInterface;

class AsteriskEventDispatcher
{
    /**
     * @param iterable<EventHandlerInterface> $handlers
     */
    public function __construct(
        private readonly iterable $handlers,
    ) {
    }

    public function dispatch(EventMessage $event, OutputInterface $output): void
    {
        $eventName = $event->getName();

        foreach ($this->handlers as $handler) {
            if ($handler->supports($eventName)) {
                $handler->handle($event, $output);
                return;
            }
        }
    }
}
