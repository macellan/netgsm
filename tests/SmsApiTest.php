<?php

namespace Macellan\Netgsm\Tests;

use DateTime;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Macellan\Netgsm\Api\BaseApi;
use Macellan\Netgsm\Api\Sms\Sms;
use Macellan\Netgsm\DTO\Sms\SmsMessage;
use Macellan\Netgsm\Exceptions\HttpClientException;
use Macellan\Netgsm\Exceptions\InvalidSmsMessageException;
use Macellan\Netgsm\Exceptions\NetgsmException;

class SmsApiTest extends TestCase
{
    private Sms $smsApi;

    public function setUp(): void
    {
        parent::setUp();

        $this->smsApi = new Sms($this->app['config']->get('services.sms.netgsm'));
    }

    private function mockSendSmsResponse(string $code, ?string $jobId = null, int $status = 200): void
    {
        Http::fake([
            BaseApi::BASE_URL.'/sms/rest/v2/send' => Http::response([
                'code' => $code,
                'jobid' => $jobId,
            ], $status)
        ]);
    }

    public function test_send_sms_success(): void
    {
        $smsMessage = (new SmsMessage('Test message'))
            ->setNumbers([fake()->e164PhoneNumber]);
        $this->mockSendSmsResponse('00', '123');

        $data = $this->smsApi->send($smsMessage);

        $this->assertEquals(['code' => '00', 'id' => '123', 'description' => null], $data);
    }

    public function test_send_sms_with_dates_and_header(): void
    {
        $smsMessage = (new SmsMessage('Test message'))
            ->setNumbers([fake()->e164PhoneNumber])
            ->setStartDate(new DateTime())
            ->setStopDate(new DateTime())
            ->setHeader('Test Header');

        $this->mockSendSmsResponse('00', '123');

        $data = $this->smsApi->send($smsMessage);

        Http::assertSent(function (Request $request) {
            return ! empty($request['startdate']) && ! empty($request['stopdate']);
        });

        $this->assertEquals(['code' => '00', 'id' => '123', 'description' => null], $data);
    }

    public function test_send_bulk_sms_same_message_to_many_numbers(): void
    {
        $message = 'Test message';
        $numbers = [fake()->e164PhoneNumber, fake()->e164PhoneNumber];

        $smsMessage = (new SmsMessage($message))
            ->setNumbers($numbers);

        $this->mockSendSmsResponse('00', '123');

        $data = $this->smsApi->send($smsMessage);

        Http::assertSent(function (Request $request) use ($message, $numbers) {
            return $request['messages'] === array_map(fn ($number) => ['msg' => $message, 'no' => $number], $numbers);
        });

        $this->assertEquals(['code' => '00', 'id' => '123', 'description' => null], $data);
    }

    public function test_send_bulk_sms_many_message_to_many_numbers(): void
    {
        $messages = ['Test message 1', 'Test message 2'];
        $numbers = [fake()->e164PhoneNumber, fake()->e164PhoneNumber];

        $smsMessage = (new SmsMessage())
            ->setMessages($messages)
            ->setNumbers($numbers);

        $this->mockSendSmsResponse('00', '123');

        $data = $this->smsApi->send($smsMessage);

        Http::assertSent(function (Request $request) use ($messages, $numbers) {
            return $request['messages'] === array_map(
                    fn($message, $number) => ['msg' => $message, 'no' => $number],
                    $messages,
                    $numbers,
                );
        });

        $this->assertEquals(['code' => '00', 'id' => '123', 'description' => null], $data);
    }

    public function test_send_sms_empty_number(): void
    {
        $exception = new InvalidSmsMessageException(trans('netgsm::errors.empty_message_or_number'));

        $this->expectExceptionObject($exception);

        $smsMessage = (new SmsMessage('Test message'));

        $this->mockSendSmsResponse('00', '123');

        $this->smsApi->send($smsMessage);
    }

    public function test_send_sms_empty_message(): void
    {
        $exception = new InvalidSmsMessageException(trans('netgsm::errors.empty_message_or_number'));

        $this->expectExceptionObject($exception);

        $smsMessage = (new SmsMessage())
            ->setNumbers([fake()->e164PhoneNumber]);

        $this->mockSendSmsResponse('00', '123');

        $this->smsApi->send($smsMessage);
    }

    public function test_send_bulk_sms_mismatched_message_count(): void
    {
        $exception = new InvalidSmsMessageException(trans('netgsm::errors.mismatched_message_count'));

        $this->expectExceptionObject($exception);

        $smsMessage = (new SmsMessage())
            ->setNumbers([fake()->e164PhoneNumber])
            ->setMessages(['Message 1', 'Message 2']);

        $this->mockSendSmsResponse('00', '123');

        $this->smsApi->send($smsMessage);
    }

    public function test_send_sms_credentials_incorrect(): void
    {
        $exception = new NetgsmException(trans('netgsm::errors.credentials_incorrect'));

        $this->expectExceptionObject($exception);

        $smsMessage = (new SmsMessage('Test message'))
            ->setNumbers([fake()->e164PhoneNumber]);

        $this->mockSendSmsResponse('30', status: 406);

        $this->smsApi->send($smsMessage);
    }

    public function test_send_sms_http_client_exception(): void
    {
        $this->expectException(HttpClientException::class);

        $smsMessage = (new SmsMessage('Test message'))
            ->setNumbers([fake()->e164PhoneNumber]);

        $this->mockSendSmsResponse('00', '123', 500);

        $this->smsApi->send($smsMessage);
    }
}
