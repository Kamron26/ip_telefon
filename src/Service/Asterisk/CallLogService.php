<?php

namespace App\Service\Asterisk;

use App\Entity\CallLog;
use Doctrine\ORM\EntityManagerInterface;
use PAMI\Message\Event\EventMessage;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\Extension;

class CallLogService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function handleDialBegin(EventMessage $event, OutputInterface $output): void
    {
        $caller = $event->getKey('CallerIDNum') ?? '';
        $callee = $event->getKey('DestCallerIDNum') ?? ($event->getKey('Exten') ?? '');
        $uniqueId = $event->getKey('Uniqueid') ?? '';
        $linkedId = $event->getKey('Linkedid') ?? $uniqueId;

        $output->writeln("DialBegin: $caller -> $callee | uid=$uniqueId | linkedid=$linkedId");

        $call = new CallLog();
        $call->setCaller($caller);
        $call->setCallee($callee);
        $fromExtension = $this->em->getRepository(Extension::class)->findOneBy([
            'number' => $caller,
        ]);

        $toExtension = $this->em->getRepository(Extension::class)->findOneBy([
            'number' => $callee,
        ]);

        $call->setFromExtension($fromExtension);
        $call->setToExtension($toExtension);
        $call->setStatus('ringing');
        $call->setUniqueid($uniqueId);
        $call->setLinkedid($linkedId);
        $call->setStartedAt(new \DateTimeImmutable());
        $call->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($call);
        $this->em->flush();
    }

    public function handleBridgeEnter(EventMessage $event, OutputInterface $output): void
    {
        $uniqueId = $event->getKey('Uniqueid') ?? '';
        $linkedId = $event->getKey('Linkedid') ?? $uniqueId;

        $output->writeln("BridgeEnter: uid=$uniqueId | linkedid=$linkedId");

        $call = $this->findCallByUniqueOrLinkedId($uniqueId, $linkedId);
        if (!$call) {
            return;
        }

        if ($call->getAnsweredAt() === null) {
            $call->setStatus('answered');
            $call->setAnsweredAt(new \DateTimeImmutable());
            $this->em->flush();
        }
    }

    public function handleHangup(EventMessage $event, OutputInterface $output): void
    {
        $uniqueId = $event->getKey('Uniqueid') ?? '';
        $linkedId = $event->getKey('Linkedid') ?? $uniqueId;

        $output->writeln("Hangup: uid=$uniqueId | linkedid=$linkedId");

        $call = $this->findCallByUniqueOrLinkedId($uniqueId, $linkedId);
        if (!$call || $call->getEndedAt() !== null) {
            return;
        }

        $call->setEndedAt(new \DateTimeImmutable());

        if ($call->getAnsweredAt()) {
            $call->setStatus('finished');
            $call->setDuration(time() - $call->getAnsweredAt()->getTimestamp());
        } else {
            $call->setStatus('missed');
        }

        $this->em->flush();
    }

    public function findCallByUniqueOrLinkedId(?string $uniqueId, ?string $linkedId): ?CallLog
    {
        $repo = $this->em->getRepository(CallLog::class);

        if ($linkedId) {
            $call = $repo->findOneBy(['linkedid' => $linkedId]);
            if ($call) {
                return $call;
            }
        }

        if ($uniqueId) {
            $call = $repo->findOneBy(['uniqueid' => $uniqueId]);
            if ($call) {
                return $call;
            }
        }

        return null;
    }
}
