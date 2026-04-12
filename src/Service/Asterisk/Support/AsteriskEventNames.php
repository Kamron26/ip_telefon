<?php

namespace App\Service\Asterisk\Support;

final class AsteriskEventNames
{
    public const DIAL_BEGIN = 'DialBegin';
    public const BRIDGE_ENTER = 'BridgeEnter';
    public const HANGUP = 'Hangup';
    public const SOFT_HANGUP_REQUEST = 'SoftHangupRequest';

    private function __construct()
    {
    }
}
