<?php

namespace Macellan\Netgsm\Tests;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Macellan\Netgsm\Api\BaseApi;
use Macellan\Netgsm\DTO\Sms\OtpSmsMessage;
use Macellan\Netgsm\DTO\Sms\SmsMessage;
use Macellan\Netgsm\Facades\Netgsm;
use Spatie\ArrayToXml\ArrayToXml;

class NetgsmFacadeTest extends TestCase
{
    public function test_send_sms(): void
    {
        $url = BaseApi::BASE_URL.'/sms/rest/v2/send';

        Http::fake([
            $url => Http::response([]),
        ]);

        $smsMessage = (new SmsMessage)
            ->setMessage('Test message')
            ->setNumbers([fake()->e164PhoneNumber]);

        Netgsm::sendSms($smsMessage);

        Http::assertSent(function (Request $request) use ($url) {
            return $request->url() == $url;
        });
    }

    public function test_send_otp_sms(): void
    {
        $url = BaseApi::BASE_URL.'/sms/send/otp';

        Http::fake([
            $url => Http::response(
                ArrayToXml::convert([], 'xml', true, 'UTF-8')
            ),
        ]);

        $otpSmsMessage = (new OtpSmsMessage('Test message'))
            ->setNumbers([fake()->e164PhoneNumber]);

        Netgsm::sendSms($otpSmsMessage);

        Http::assertSent(function (Request $request) use ($url) {
            return $request->url() == $url;
        });
    }
}
