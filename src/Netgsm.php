<?php

namespace Macellan\Netgsm;

use Macellan\Netgsm\Api\Sms\OtpSms;
use Macellan\Netgsm\Api\Sms\Sms;
use Macellan\Netgsm\DTO\Sms\BaseSmsMessage;
use Macellan\Netgsm\DTO\Sms\OtpSmsMessage;
use Macellan\Netgsm\DTO\Sms\SmsMessage;
use Macellan\Netgsm\Exceptions\NetgsmException;

class Netgsm
{
    public function __construct(private readonly array $config)
    {
        //
    }

    /**
     * @throws NetgsmException
     */
    public function sendSms(BaseSmsMessage $smsMessage): array
    {
        return match (true) {
            $smsMessage instanceof SmsMessage => (new Sms($this->config))->send($smsMessage),
            $smsMessage instanceof OtpSmsMessage => (new OtpSms($this->config))->send($smsMessage),
            default => throw new NetgsmException('Undefined message object'),
        };
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}
