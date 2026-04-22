<?php

namespace App\Service\Asterisk;

use PAMI\Message\Event\EventMessage;
use Symfony\Component\Console\Output\OutputInterface;

class AsteriskEventHandler
{
    public function __construct(
        private readonly CallLogService $callLogService,
        private readonly CallEventService $callEventService,
        private readonly RecordingService $recordingService,
    ) {
    }

    public function handle(EventMessage $event, OutputInterface $output): void
    {
        $eventName = $event->getName();
        $output->writeln('Event: ' . $eventName);

        $this->callEventService->storeRawEvent($event);

        if ($eventName === 'DialBegin') {
            $this->callLogService->handleDialBegin($event, $output);
        }

        if ($eventName === 'BridgeEnter') {
            $this->callLogService->handleBridgeEnter($event, $output);
        }

        if ($eventName === 'Hangup') {
            $this->callLogService->handleHangup($event, $output);
        }

        if ($eventName === 'MixMonitorStop') {
            $this->recordingService->handleMixMonitorStop($event, $output);
        }
    }
}
