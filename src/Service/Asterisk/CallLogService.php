<?php

namespace App\Service\Asterisk;

use App\Entity\CallLog;
use Doctrine\ORM\EntityManagerInterface;
use PAMI\Message\Event\EventMessage;
use Symfony\Component\Console\Output\OutputInterface;

class CallLogService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function handleDialBegin(EventMessage $event, OutputInterface $output): void
    {
        $caller = $event->getKey('CallerIDNum') ?? '';
        $callee = $event->getKey('DestCallerIDNum') ?? '';
        $uniqueId = $event->getKey('Uniqueid') ?? '';

        $output->writeln("DialBegin: $caller -> $callee | uid=$uniqueId");

        $call = new CallLog();
        $call->setCaller($caller);
        $call->setCallee($callee);
        $call->setStatus('ringing');
        $call->setUniqueid($uniqueId);
        $call->setStartedAt(new \DateTimeImmutable());
        $call->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($call);
        $this->em->flush();
    }

    public function handleBridgeEnter(EventMessage $event, OutputInterface $output): void
    {
        $uniqueId = $event->getKey('Uniqueid') ?? '';
        $output->writeln("BridgeEnter: uid=$uniqueId");

        $call = $this->em->getRepository(CallLog::class)
            ->findOneBy(['uniqueid' => $uniqueId]);

        if (!$call) {
            return;
        }

        if ($call->getAnsweredAt() === null) {
            $call->setStatus('answered');
            $call->setAnsweredAt(new \DateTimeImmutable());
            $this->em->flush();
        }

        $output->writeln("Answered: uid=$uniqueId");
    }

    public function handleHangup(EventMessage $event, OutputInterface $output): void
    {
        $uniqueId = $event->getKey('Uniqueid') ?? '';
        $output->writeln("Hangup: uid=$uniqueId");

        $call = $this->em->getRepository(CallLog::class)
            ->findOneBy(['uniqueid' => $uniqueId]);

        if (!$call) {
            return;
        }

        $call->setEndedAt(new \DateTimeImmutable());

        if ($call->getAnsweredAt()) {
            $call->setStatus('finished');
            $duration = time() - $call->getAnsweredAt()->getTimestamp();
            $call->setDuration($duration);
        } else {
            $call->setStatus('missed');
        }

        $this->em->flush();
    }
}
