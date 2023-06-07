<?php

namespace Macellan\Netgsm\Api\Sms;

use Illuminate\Http\Client\Response;
use Macellan\Netgsm\Api\BaseApi;
use Macellan\Netgsm\DTO\Sms\OtpSmsMessage;
use Macellan\Netgsm\Exceptions\NetgsmException;

class OtpSms extends BaseApi
{
    private const ERROR_CODES = [
        '20' => 'netgsm::errors.message_incorrect',
        '30' => 'netgsm::errors.credentials_incorrect',
        '40' => 'netgsm::errors.sender_incorrect',
        '41' => 'netgsm::errors.sender_incorrect',
        '50' => 'netgsm::errors.receiver_incorrect',
        '51' => 'netgsm::errors.receiver_incorrect',
        '52' => 'netgsm::errors.receiver_incorrect',
        '60' => 'netgsm::errors.otp_account_not_defined',
        '70' => 'netgsm::errors.check_input_parameters',
        '80' => 'netgsm::errors.query_limit_exceeded',
        '100' => 'netgsm::errors.system_error',
    ];

    /**
     * @throws NetgsmException
     */
    public function send(OtpSmsMessage $message): array
    {
         $response = $this->xmlRequest('/sms/send/otp', $this->getRequestData($message), 'mainbody');

         return $this->parseResponse($response);
    }

    /**
     * @throws NetgsmException
     */
    protected function checkErrors(Response $response): void
    {
        $code = $this->parseResponse($response)['code'];

        if (in_array($code, array_keys(self::ERROR_CODES))) {
            throw new NetgsmException(
                sprintf(
                    'Code: %s - Description: %s',
                    $code,
                    trans(self::ERROR_CODES[$code]
                    )
                )
            );
        }
    }

    /**
     * @throws NetgsmException
     */
    private function parseResponse(Response $response): array
    {
        $xml = $this->parseResponseToXml($response);

        return [
            'code' => (string) $xml->main->code,
            'id' => (string) $xml->main?->jobID,
            'error' => (string) $xml->main?->error,
        ];
    }

    private function getRequestData(OtpSmsMessage $message): array
    {
        $data = [
            'header' => [
                'usercode' => $this->getUserName(),
                'password' => $this->getPassword(),
                'msgheader' => $message->getHeader() ?? $this->getMessageHeader(),
            ],
            'body' => [
                'msg' => [
                    '_cdata' => $message->getMessage(),
                ],
                'no' => [],
            ],
        ];

        foreach ($message->getNumbers() as $number) {
            $data['body']['no'][] = $number;
        }

        return $data;
    }
}
