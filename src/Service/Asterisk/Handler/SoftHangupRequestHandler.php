<?php

namespace App\Service\Asterisk\Handler;

use App\Service\Asterisk\CallLogService;
use App\Service\Asterisk\Support\AsteriskEventNames;
use PAMI\Message\Event\EventMessage;
use Symfony\Component\Console\Output\OutputInterface;

class SoftHangupRequestHandler implements EventHandlerInterface
{
    public function __construct(
        private readonly CallLogService $callLogService,
    ) {
    }

    public function supports(string $eventName): bool
    {
        return $eventName === 'SoftHangupRequest';
    }

    public function handle(EventMessage $event, OutputInterface $output): void
    {
        $this->callLogService->handleHangup($event, $output);
    }
}
