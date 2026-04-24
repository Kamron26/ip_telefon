<?php

namespace App\Service\Asterisk;

use App\Entity\Extension;
use Doctrine\ORM\EntityManagerInterface;

class ExtensionStatusService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function updateStatus(string $number, string $status): void
    {
        if ($number === '') {
            return;
        }

        $extension = $this->em->getRepository(Extension::class)->findOneBy([
            'number' => $number,
        ]);

        if (!$extension) {
            return;
        }

        $extension->setStatus($status);
        $this->em->flush();
    }

    public function normalizeDeviceStatus(?string $deviceStatus): string
    {
        return match ($deviceStatus) {
            'INUSE', 'BUSY', 'ONHOLD' => 'busy',
            'RINGING', 'RINGINUSE' => 'ringing',
            'NOT_INUSE' => 'online',
            'UNAVAILABLE', 'UNKNOWN', 'INVALID' => 'offline',
            default => 'offline',
        };
    }

    public function normalizeContactStatus(?string $contactStatus): string
    {
        return match ($contactStatus) {
            'Reachable' => 'online',
            'Unreachable', 'Removed', 'NonQualified' => 'offline',
            default => 'offline',
        };
    }

    public function extractExtensionFromDevice(?string $device): ?string
    {
        if (!$device) {
            return null;
        }

        if (preg_match('/PJSIP\/([0-9]+)/', $device, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public function extractExtensionFromEndpoint(?string $endpoint): ?string
    {
        if (!$endpoint) {
            return null;
        }

        if (preg_match('/^([0-9]+)/', $endpoint, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
