<?php

namespace Macellan\Netgsm\Tests;

use Illuminate\Support\Facades\Http;
use Macellan\Netgsm\Api\BaseApi;
use Macellan\Netgsm\Api\Sms\OtpSms;
use Macellan\Netgsm\Api\Sms\Sms;
use Macellan\Netgsm\DTO\Sms\OtpSmsMessage;
use Macellan\Netgsm\Exceptions\NetgsmException;
use Spatie\ArrayToXml\ArrayToXml;

class OtpSmsApiTest extends TestCase
{
    private OtpSms $otpSmsApi;

    public function setUp(): void
    {
        parent::setUp();

        $this->otpSmsApi = new OtpSms($this->app['config']->get('services.sms.netgsm'));
    }

    private function initOtpSmsMessage(): OtpSmsMessage
    {
        return (new OtpSmsMessage('Test message'))
            ->setNumbers(['123456']);
    }

    private function mockSendOtpSmsResponse(string $code, ?string $jobId = null, ?string $error = null): void
    {
        $responseArr = [
            'main' => [
                'code' => $code,
                'jobID' => $jobId,
                'error' => $error,
            ],
        ];

        Http::fake([
            BaseApi::BASE_URL.'/sms/send/otp' => Http::response(
                ArrayToXml::convert($responseArr, 'xml', true, 'UTF-8')
            )
        ]);
    }

    public function test_send_otp_sms_success(): void
    {
        $otpSmsMessage = $this->initOtpSmsMessage();
        $this->mockSendOtpSmsResponse('0', '123');

        $data = $this->otpSmsApi->send($otpSmsMessage);

        $this->assertEquals(['code' => '0', 'id' => '123', 'error' => null], $data);
    }

    public function test_send_otp_sms_response_error_code(): void
    {
        $this->expectException(NetgsmException::class);

        $otpSmsMessage = $this->initOtpSmsMessage();
        $this->mockSendOtpSmsResponse('20', null, 'Error');

        $data = $this->otpSmsApi->send($otpSmsMessage);

        $this->assertEquals(['code' => '20', 'id' => null, 'error' => 'Error'], $data);
    }

    public function test_send_otp_sms_xml_parse_exception(): void
    {
        $this->expectException(NetgsmException::class);

        $otpSmsMessage = $this->initOtpSmsMessage();
        Http::fake([
            '*/sms/send/otp' => Http::response('Wrong data')
        ]);

        $this->otpSmsApi->send($otpSmsMessage);
    }
}
