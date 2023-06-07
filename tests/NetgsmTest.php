<?php

namespace Macellan\Netgsm\Tests;

use Illuminate\Support\Facades\Http;
use Macellan\Netgsm\Api\BaseApi;
use Macellan\Netgsm\DTO\Sms\SmsMessage;
use Macellan\Netgsm\Netgsm;

class NetgsmTest extends TestCase
{
    private Netgsm $netgsm;

    public function setUp(): void
    {
        parent::setUp();

        $this->netgsm = new Netgsm($this->app['config']->get('services.sms.netgsm'));
    }

    public function test_send_sms(): void
    {
        Http::fake([
            BaseApi::BASE_URL.'/*' => Http::response('')
        ]);

        $smsMessage = (new SmsMessage('Test message'))
            ->setNumbers(['123456']);

        $this->assertIsArray($this->netgsm->sendSms($smsMessage));
    }
}
