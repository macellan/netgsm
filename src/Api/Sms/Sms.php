<?php

namespace Macellan\Netgsm\Api\Sms;

use Illuminate\Http\Client\Response;
use Macellan\Netgsm\Api\BaseApi;
use Macellan\Netgsm\DTO\Sms\SmsMessage;
use Macellan\Netgsm\Exceptions\InvalidSmsMessageException;
use Macellan\Netgsm\Exceptions\NetgsmException;

class Sms extends BaseApi
{
    private const ERROR_CODES = [
        '20' => 'netgsm::errors.message_incorrect',
        '30' => 'netgsm::errors.credentials_incorrect',
        '40' => 'netgsm::errors.sender_incorrect',
        '50' => 'netgsm::errors.iys_account_not_send',
        '51' => 'netgsm::errors.iys_brand_not_found',
        '70' => 'netgsm::errors.parameters_incorrect',
        '80' => 'netgsm::errors.exceeded_sending_limit',
        '85' => 'netgsm::errors.exceeded_duplicate_sending_limit',
    ];

    /**
     * @throws NetgsmException
     */
    public function send(SmsMessage $message): array
    {
        $response = $this->jsonRequest('post', '/sms/rest/v2/send', $this->getRequestData($message));

        return $this->parseResponse($response);
    }

    /**
     * @throws NetgsmException
     */
    protected function checkErrors(Response $response): void
    {
        $code = $this->parseResponse($response)['code'];

        if ($code && in_array($code, array_keys(self::ERROR_CODES))) {
            throw new NetgsmException(
                sprintf(
                    'Code: %s - Description: %s',
                    $code,
                    trans(self::ERROR_CODES[$code])
                )
            );
        }
    }

    private function parseResponse(Response $response): array
    {
        return [
            'code' => $response->json('code'),
            'id' => $response->json('jobid'),
            'description' => $response->json('description'),
        ];
    }

    private function getRequestData(SmsMessage $message): array
    {
        $data = [
            'msgheader' => $message->getHeader() ?? $this->getMessageHeader(),
            'messages' => $this->getMessagesToApiFormat($message),
            'encoding' => $message->getEncoding(),
            'iysfilter' => $message->getIysFilter(),
            'partnercode' => $message->getPartnerCode(),
            'appname' => $message->getAppName(),
            'startdate' => $message->getStartDate()?->format('dmYHi'),
            'stopdate' => $message->getStopDate()?->format('dmYHi'),
        ];

        return array_filter($data, function ($value) {
            return $value !== null;
        });
    }

    private function getMessagesToApiFormat(SmsMessage $message): array
    {
        if (! $message->getNumbers() || (! $message->getMessage() && ! $message->getMessages())) {
            throw new InvalidSmsMessageException(trans('netgsm::errors.empty_message_or_number'));
        }

        if ($message->getMessage()) {
            return array_map(fn ($number) => ['msg' => $message->getMessage(), 'no' => $number], $message->getNumbers());
        }

        if (count($message->getMessages()) !== count($message->getNumbers())) {
            throw new InvalidSmsMessageException(trans('netgsm::errors.mismatched_message_count'));
        }

        return array_map(
            fn ($message, $number) => ['msg' => $message, 'no' => $number],
            $message->getMessages(),
            $message->getNumbers(),
        );
    }
}
