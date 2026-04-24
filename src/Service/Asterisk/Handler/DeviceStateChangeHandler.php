<?php

namespace App\Service\Asterisk\Handler;

use App\Service\Asterisk\ExtensionStatusService;
use PAMI\Message\Event\EventMessage;
use Symfony\Component\Console\Output\OutputInterface;

class DeviceStateChangeHandler implements EventHandlerInterface
{
    public function __construct(
        private readonly ExtensionStatusService $extensionStatusService,
    ) {
    }

    public function supports(string $eventName): bool
    {
        return $eventName === 'DeviceStateChange';
    }

    public function handle(EventMessage $event, OutputInterface $output): void
    {
        $device = $event->getKey('Device') ?? '';
        $deviceState = $event->getKey('State') ?? '';

        $output->writeln('Device raw: ' . $device);
        $output->writeln('State raw: ' . $deviceState);

        $number = $this->extensionStatusService->extractExtensionFromDevice($device);

        $output->writeln('Extracted number: ' . ($number ?? 'null'));

        if (!$number) {
            return;
        }

        $status = $this->extensionStatusService->normalizeDeviceStatus($deviceState);

        $this->extensionStatusService->updateStatus($number, $status);

        $output->writeln("Extension {$number} status updated from DeviceStateChange: {$status}");
    }
}
