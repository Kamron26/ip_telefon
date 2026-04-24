<?php

namespace App\Service\Asterisk;

use App\Entity\Extension;
use Doctrine\ORM\EntityManagerInterface;

class AsteriskPjsipConfigGeneratorService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly string $outputFile,
    ) {
    }

    public function generate(): int
    {
        $extensions = $this->em->getRepository(Extension::class)->findBy([
            'isActive' => true,
        ]);

        $content = "; AUTO GENERATED FILE. DO NOT EDIT MANUALLY.\n\n";
        $count = 0;

        foreach ($extensions as $extension) {
            $number = $extension->getNumber();
            $password = $extension->getPassword();

            if (!$number || !$password) {
                continue;
            }

            if (!preg_match('/^[0-9]{2,10}$/', $number)) {
                continue;
            }

            $content .= <<<CONF
; ==================== ENDPOINT {$number} ====================
[{$number}]
type=endpoint
context=internal
disallow=all
allow=ulaw
allow=alaw
auth={$number}-auth
aors={$number}
direct_media=no
rtp_symmetric=yes
force_rport=yes
rewrite_contact=yes

[{$number}-auth]
type=auth
auth_type=userpass
username={$number}
password={$password}

[{$number}]
type=aor
max_contacts=5
remove_existing=yes
qualify_frequency=30

CONF;

            $count++;
        }

        file_put_contents($this->outputFile, $content);

        return $count;
    }
}
