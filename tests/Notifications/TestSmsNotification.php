<?php

namespace Macellan\Netgsm\Tests\Notifications;

use Illuminate\Notifications\Notification;
use Macellan\Netgsm\DTO\Sms\OtpSmsMessage;
use Macellan\Netgsm\DTO\Sms\SmsMessage;

class TestSmsNotification extends Notification
{
    public function __construct(private bool $isOtpSms = false)
    {
        //
    }

    public function toNetgsm()
    {
        if ($this->isOtpSms) {
            return new OtpSmsMessage('Test otp message');
        }

        return new SmsMessage('Test sms message');
    }
}
