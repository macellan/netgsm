<?php

namespace Macellan\Netgsm\Tests;

use DateTime;
use Illuminate\Support\Facades\Http;
use Macellan\Netgsm\Api\BaseApi;
use Macellan\Netgsm\Api\Sms\Sms;
use Macellan\Netgsm\DTO\Sms\SmsMessage;
use Macellan\Netgsm\Enums\SmsSendType;
use Macellan\Netgsm\Exceptions\HttpClientException;
use Macellan\Netgsm\Exceptions\NetgsmException;

class SmsApiTest extends TestCase
{
    private Sms $smsApi;

    public function setUp(): void
    {
        parent::setUp();

        $this->smsApi = new Sms($this->app['config']->get('services.sms.netgsm'));
    }

    private function initSmsMessage(): SmsMessage
    {
        return (new SmsMessage('Test message'))
            ->setNumbers(['123456']);
    }

    private function mockSendSmsResponse(string $code, ?string $id = null): void
    {
        Http::fake([
            BaseApi::BASE_URL.'/sms/send/xml' => Http::response($code.' '.$id)
        ]);
    }

    public function test_send_sms_success(): void
    {
        $smsMessage = $this->initSmsMessage();
        $this->mockSendSmsResponse('00', '123');

        $data = $this->smsApi->send($smsMessage);

        $this->assertEquals(['code' => '00', 'id' => '123'], $data);
    }

    public function test_send_sms_with_dates_and_header(): void
    {
        $smsMessage = ($this->initSmsMessage())
            ->setStartDate(new DateTime())
            ->setStopDate(new DateTime())
            ->setHeader('Test Header');

        $this->mockSendSmsResponse('00', '123');

        $data = $this->smsApi->send($smsMessage);

        $this->assertEquals(['code' => '00', 'id' => '123'], $data);
    }

    public function test_send_sms_many_to_many(): void
    {
        $smsMessage = ($this->initSmsMessage())
            ->setType(SmsSendType::MANY_TO_MANY)
            ->setManyToData([
                ['message' => 'Test message', 'number' => '123456'],
                ['message' => 'Test message 2', 'number' => '1234567'],
            ]);

        $this->mockSendSmsResponse('00', '123');

        $data = $this->smsApi->send($smsMessage);

        $this->assertEquals(['code' => '00', 'id' => '123'], $data);
    }

    public function test_send_sms_response_error_code(): void
    {
        $this->expectException(NetgsmException::class);

        $smsMessage = $this->initSmsMessage();
        $this->mockSendSmsResponse('20');

        $data = $this->smsApi->send($smsMessage);

        $this->assertEquals(['code' => '20', 'id' => null], $data);
    }

    public function test_send_sms_http_client_exception(): void
    {
        $this->expectException(HttpClientException::class);
        $this->expectException(\Illuminate\Http\Client\RequestException::class);

        $smsMessage = $this->initSmsMessage();
        Http::fake([
            '*/sms/send/xml' => Http::response('', 500)
        ]);

        $this->smsApi->send($smsMessage);
    }
}
