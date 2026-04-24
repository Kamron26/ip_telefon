<?php

namespace App\Service\Asterisk\Handler;

use App\Service\Asterisk\ExtensionStatusService;
use PAMI\Message\Event\EventMessage;
use Symfony\Component\Console\Output\OutputInterface;

class ContactStatusHandler implements EventHandlerInterface
{
    public function __construct(
        private readonly ExtensionStatusService $extensionStatusService,
    ) {
    }

    public function supports(string $eventName): bool
    {
        return $eventName === 'ContactStatus';
    }

    public function handle(EventMessage $event, OutputInterface $output): void
    {
        $endpointName = $event->getKey('EndpointName')
            ?? $event->getKey('AOR')
            ?? '';

        $contactStatus = $event->getKey('ContactStatus')
            ?? $event->getKey('Status')
            ?? '';

        $number = $this->extensionStatusService->extractExtensionFromEndpoint($endpointName);

        if (!$number) {
            return;
        }

        $status = $this->extensionStatusService->normalizeContactStatus($contactStatus);

        $this->extensionStatusService->updateStatus($number, $status);

        $output->writeln("Extension {$number} status updated from ContactStatus: {$status}");
    }
}
