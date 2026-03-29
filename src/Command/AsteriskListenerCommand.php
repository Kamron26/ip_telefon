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
            'host' => $_ENV['ASTERISK_HOST'] ?? 'asterisk',
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
        $output->writeln("Waiting for events...");

        // PAMI 1.x da ishlaydigan universal loop
        while (true) {
            // Ma'lumotlarni qabul qilish va qayta ishlash
            $client->process();

            // Eventlarni olishning 2 xil usuli (qaysi biri ishlasa)
            // Usul 1: getEvents() array qaytaradi
            if (method_exists($client, 'getEvents')) {
                $events = $client->getEvents();
                if (!empty($events)) {
                    foreach ($events as $event) {
                        $this->handleEvent($event, $output);
                    }
                }
            }

            // Usul 2: getEvent() object qaytaradi (agar mavjud bo'lsa)
            if (method_exists($client, 'getEvent')) {
                while ($event = $client->getEvent()) {
                    $this->handleEvent($event, $output);
                }
            }

            // CPU yuklamasligi uchun qisqa kutish
            usleep(50000); // 0.05 sekund
        }

        return Command::SUCCESS;
    }

    private function handleEvent(EventMessage $event, OutputInterface $output)
    {
        $eventName = $event->getName();

        // Debug uchun - barcha eventlarni chiqaramiz
        $output->writeln("Event received: " . $eventName);

        // 📞 CALL BOSHLANDI
        if ($eventName === 'Dial') {
            $caller = $event->getKey('CallerIDNum');
            $callee = $event->getKey('Destination');

            if ($caller && $callee) {
                $call = new CallLog();
                $call->setCaller($caller);
                $call->setCallee($callee);
                $call->setStatus('ringing');
                $call->setStartedAt(new \DateTime());

                $this->em->persist($call);
                $this->em->flush();

                $output->writeln("📞 Dial: $caller → $callee");
            }
        }

        // 📞 JAVOB BERILDI
        if ($eventName === 'BridgeEnter') {
            $caller = $event->getKey('CallerIDNum');

            if ($caller) {
                $call = $this->em->getRepository(CallLog::class)
                    ->findOneBy(['caller' => $caller, 'status' => 'ringing'], ['id' => 'DESC']);

                if ($call) {
                    $call->setStatus('answered');
                    $call->setAnsweredAt(new \DateTime());
                    $this->em->flush();

                    $output->writeln("✅ Answered: $caller");
                }
            }
        }

        // 📞 TUGADI
        if ($eventName === 'Hangup') {
            $caller = $event->getKey('CallerIDNum');

            if ($caller) {
                $call = $this->em->getRepository(CallLog::class)
                    ->findOneBy(['caller' => $caller], ['id' => 'DESC']);

                if ($call && $call->getStatus() !== 'finished') {
                    $call->setStatus('finished');
                    $call->setEndedAt(new \DateTime());

                    if ($call->getAnsweredAt()) {
                        $duration = $call->getEndedAt()->getTimestamp() - $call->getAnsweredAt()->getTimestamp();
                        $call->setDuration($duration);
                    }

                    $this->em->flush();

                    $output->writeln("❌ Hangup: $caller");
                }
            }
        }
    }
}
