<?php

namespace App\Service\Asterisk\Handler;

use App\Service\Asterisk\CallLogService;
use App\Service\Asterisk\Support\AsteriskEventNames;
use PAMI\Message\Event\EventMessage;
use Symfony\Component\Console\Output\OutputInterface;

class DialBeginHandler implements EventHandlerInterface
{
    public function __construct(
        private readonly CallLogService $callLogService,
    ) {
    }

    public function supports(string $eventName): bool
    {
        return $eventName === AsteriskEventNames::DIAL_BEGIN;
    }

    public function handle(EventMessage $event, OutputInterface $output): void
    {
        $this->callLogService->handleDialBegin($event, $output);
    }
}
