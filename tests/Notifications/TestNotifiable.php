<?php

namespace Macellan\Netgsm\Tests\Notifications;

use Illuminate\Notifications\Notifiable;

class TestNotifiable
{
    use Notifiable;

    public function routeNotificationForSms(): string
    {
        return '+905554443322';
    }
}
