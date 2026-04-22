<?php

namespace App\Service\Asterisk;

use App\Entity\Recording;
use Doctrine\ORM\EntityManagerInterface;
use PAMI\Message\Event\EventMessage;
use Symfony\Component\Console\Output\OutputInterface;

class RecordingService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly CallLogService $callLogService,
        private readonly string $recordingBaseDir,
    ) {
    }

    public function handleMixMonitorStop(EventMessage $event, OutputInterface $output): void
    {
        $uniqueId = $event->getKey('Uniqueid') ?? '';
        $linkedId = $event->getKey('Linkedid') ?? $uniqueId;

        $output->writeln("MixMonitorStop: uid=$uniqueId | linkedid=$linkedId");

        $call = $this->callLogService->findCallByUniqueOrLinkedId($uniqueId, $linkedId);
        if (!$call) {
            $output->writeln('Recording skip: call not found');
            return;
        }

        $filePath = $this->findRecordingFile($linkedId, $uniqueId);
        if (!$filePath) {
            $output->writeln('Recording skip: file not found');
            return;
        }

        $existing = $this->em->getRepository(Recording::class)->findOneBy([
            'cal' => $call,
            'filePath' => $filePath,
        ]);

        if ($existing) {
            $output->writeln('Recording already exists');
            return;
        }

        $recording = new Recording();
        $recording->setCal($call);
        $recording->setFilePath($filePath);
        $recording->setFileSize(@filesize($filePath) ?: null);
        $recording->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($recording);
        $this->em->flush();

        $output->writeln("Recording saved: $filePath");
    }

    private function findRecordingFile(?string $linkedId, ?string $uniqueId): ?string
    {
        $ids = array_filter([$linkedId, $uniqueId]);

        if (!$ids || !is_dir($this->recordingBaseDir)) {
            return null;
        }

        $extensions = ['wav', 'WAV', 'mp3', 'ogg', 'gsm'];
        $matches = [];

        foreach ($ids as $id) {
            foreach ($extensions as $ext) {
                $pattern = rtrim($this->recordingBaseDir, '/') . "/*{$id}*.{$ext}";
                foreach (glob($pattern) ?: [] as $file) {
                    if (is_file($file)) {
                        $matches[$file] = filemtime($file) ?: 0;
                    }
                }
            }
        }

        if (!$matches) {
            return null;
        }

        arsort($matches);

        return array_key_first($matches);
    }
}
