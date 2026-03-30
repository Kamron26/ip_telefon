<?php

namespace App\Command;


use App\Service\Asterisk\AsteriskEventHandler;
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
        private readonly AsteriskEventHandler $eventHandler,
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
            $this->eventHandler->handle($event, $output);
        });

        while (true) {
            $client->process();
            usleep(200000);
        }
    }
}
