<?php

namespace App\Command;

use PAMI\Client\Impl\ClientImpl;
use PAMI\Message\Event\EventMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\CallLog;

#[AsCommand(
    name: 'app:asterisk-listener',
    description: 'Asterisk AMI connection listener for call tracking',
)]
class AsteriskListenerCommand extends Command
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $options = [
            'host' => $_ENV['ASTERISK_HOST'] ?? '127.0.0.1',
            'scheme' => 'tcp://',
            'port' => $_ENV['ASTERISK_PORT'] ?? 5038,
            'username' => $_ENV['ASTERISK_USER'] ?? 'admin',
            'secret' => $_ENV['ASTERISK_PASS'] ?? '1234',
            'connect_timeout' => 10,
            'read_timeout' => 10,
        ];

        $client = new ClientImpl($options);
        $client->open();

        $output->writeln("Asterisk listener started...");

        while (true) {
            $client->process();

            foreach ($client->getEvents() as $event) {
                $this->handleEvent($event, $output);
            }
        }

        return Command::SUCCESS;
    }

    private function handleEvent(EventMessage $event, OutputInterface $output)
    {
        $eventName = $event->getName();

        if ($eventName === 'Dial') {
            $caller = $event->getKey('CallerIDNum');
            $callee = $event->getKey('Destination');

            $call = new CallLog();
            $call->setCaller($caller);
            $call->setCallee($callee);
            $call->setStatus('ringing');
            $call->setStartedAt(new \DateTime());

            $this->em->persist($call);
            $this->em->flush();

            $output->writeln("Dial: $caller → $callee");
        }

        if ($eventName === 'BridgeEnter') {
            $caller = $event->getKey('CallerIDNum');

            $call = $this->em->getRepository(CallLog::class)
                ->findOneBy(['caller' => $caller], ['id' => 'DESC']);

            if ($call) {
                $call->setStatus('answered');
                $call->setAnsweredAt(new \DateTime());
                $this->em->flush();
                $output->writeln("Answered: $caller");
            }
        }

        if ($eventName === 'Hangup') {
            $caller = $event->getKey('CallerIDNum');

            $call = $this->em->getRepository(CallLog::class)
                ->findOneBy(['caller' => $caller], ['id' => 'DESC']);

            if ($call) {
                $call->setStatus('finished');
                $call->setEndedAt(new \DateTime());

                if ($call->getAnsweredAt()) {
                    $duration = time() - $call->getAnsweredAt()->getTimestamp();
                    $call->setDuration($duration);
                }

                $this->em->flush();
                $output->writeln("Hangup: $caller");
            }
        }
    }
}
