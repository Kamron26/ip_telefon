<?php

namespace App\Service\Asterisk;

use App\Entity\CallEvent;
use App\Entity\CallLog;
use Doctrine\ORM\EntityManagerInterface;
use PAMI\Message\Event\EventMessage;

class CallEventService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function storeRawEvent(EventMessage $event): void
    {
        $eventEntity = new CallEvent();
        $eventEntity->setEventType($event->getName());
        $eventEntity->setEventTime(new \DateTime());
        $eventEntity->setRowData($event->getKeys());
        $eventEntity->setCreatedAt(new \DateTimeImmutable());

        $uniqueId = $event->getKey('Uniqueid') ?? null;

        if ($uniqueId) {
            $call = $this->em->getRepository(CallLog::class)
                ->findOneBy(['uniqueid' => $uniqueId]);

            if ($call) {
                $eventEntity->setCal($call);
            }
        }

        $this->em->persist($eventEntity);
        $this->em->flush();
    }
}
