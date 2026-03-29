<?php

namespace App\Command;

use App\Entity\CallLog;
use Doctrine\ORM\EntityManagerInterface;
use PAMI\Client\Impl\ClientImpl;
use PAMI\Message\Event\EventMessage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:asterisk-listener',
    description: 'Listen Asterisk AMI events',
)]
class AsteriskListenerCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $options = [
            'host' => 'host.docker.internal',
            'scheme' => 'tcp://',
            'port' => 5038,
            'username' => 'admin',
            'secret' => '1234',
            'connect_timeout' => 10,
            'read_timeout' => 10,
        ];

        $client = new ClientImpl($options);
        $client->open();

        $output->writeln('Asterisk listener started...');
        $output->writeln('Waiting for events...');

        $client->registerEventListener(function (EventMessage $event) use ($output) {
            $this->handleEvent($event, $output);
        });

        while (true) {
            $client->process();
            usleep(200000);
        }
    }

    private function handleEvent(EventMessage $event, OutputInterface $output): void
    {
        $eventName = $event->getName();
        $output->writeln('Event: ' . $eventName);

        if ($eventName === 'DialBegin') {
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

        if ($eventName === 'BridgeEnter') {
            $uniqueId = $event->getKey('Uniqueid') ?? '';

            $output->writeln("BridgeEnter: uid=$uniqueId");

            $call = $this->em->getRepository(CallLog::class)
                ->findOneBy(['uniqueid' => $uniqueId]);

            if ($call) {
                $call->setStatus('answered');
                $call->setAnsweredAt(new \DateTimeImmutable());
                $this->em->flush();

                $output->writeln("Answered: uid=$uniqueId");
            }
        }

        if ($eventName === 'Hangup') {
            $uniqueId = $event->getKey('Uniqueid') ?? '';

            $output->writeln("Hangup: uid=$uniqueId");

            $call = $this->em->getRepository(CallLog::class)
                ->findOneBy(['uniqueid' => $uniqueId]);

            if ($call) {
                $call->setStatus('finished');
                $call->setEndedAt(new \DateTimeImmutable());

                if ($call->getAnsweredAt()) {
                    $duration = time() - $call->getAnsweredAt()->getTimestamp();
                    $call->setDuration($duration);
                }

                $this->em->flush();
            }
        }
    }
}
