<?php

namespace App\Service\Asterisk\Handler;

use PAMI\Message\Event\EventMessage;
use Symfony\Component\Console\Output\OutputInterface;

interface EventHandlerInterface
{
    public function supports(string $eventName): bool;

    public function handle(EventMessage $event, OutputInterface $output): void;
}
