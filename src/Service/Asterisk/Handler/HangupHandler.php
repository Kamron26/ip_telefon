<?php

namespace App\Service\Asterisk\Handler;

use App\Service\Asterisk\CallLogService;
use App\Service\Asterisk\RecordingService;
use PAMI\Message\Event\EventMessage;
use Symfony\Component\Console\Output\OutputInterface;

class HangupHandler implements EventHandlerInterface
{
    public function __construct(
        private readonly CallLogService $callLogService,
        private readonly RecordingService $recordingService,
    ) {
    }

    public function supports(string $eventName): bool
    {
        return $eventName === 'Hangup';
    }

    public function handle(EventMessage $event, OutputInterface $output): void
    {
        $this->callLogService->handleHangup($event, $output);

        $uniqueId = $event->getKey('Uniqueid') ?? '';
        $linkedId = $event->getKey('Linkedid') ?? $uniqueId;

        usleep(2000000);

        $this->recordingService->saveForCall($uniqueId, $linkedId, $output);
    }
}
