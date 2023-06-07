<?php

namespace Macellan\Netgsm\DTO\Sms;

class OtpSmsMessage extends BaseSmsMessage
{
    public function __construct(?string $message = null)
    {
        $this->message = $message;
    }
}
