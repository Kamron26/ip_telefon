<?php

namespace App\Command;

use App\Service\Asterisk\AsteriskPjsipConfigGeneratorService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:asterisk:sync-extensions',
    description: 'Generate Asterisk PJSIP extensions from database'
)]
class AsteriskSyncExtensionsCommand extends Command
{
    public function __construct(
        private readonly AsteriskPjsipConfigGeneratorService $generatorService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $count = $this->generatorService->generate();

        $output->writeln("Generated {$count} extensions for Asterisk.");

        return Command::SUCCESS;
    }
}
