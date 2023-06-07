<?php

namespace Macellan\Netgsm\Api\Sms;

use Illuminate\Http\Client\Response;
use Macellan\Netgsm\Api\BaseApi;
use Macellan\Netgsm\DTO\Sms\SmsMessage;
use Macellan\Netgsm\Enums\SmsSendType;
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
        $response = $this->xmlRequest('/sms/send/xml', $this->getRequestData($message), 'mainbody');

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

    private function parseResponse(Response $response): array
    {
        $data = explode(' ', $response->body());

        return [
            'code' => $data[0],
            'id' => $data[1] ?? '',
        ];
    }

    private function getRequestData(SmsMessage $message): array
    {
        $data = [
            'header' => [
                'company' => [
                    '_attributes' => ['dil' => $this->config['language'] ?? 'tr'],
                    '_value' => 'Netgsm',
                ],
                'usercode' => $this->getUserName(),
                'password' => $this->getPassword(),
                'type' => $message->getType()->value,
                'msgheader' => $message->getHeader() ?? $this->getMessageHeader(),
                'filter' => $message->getFilter(),
            ],
            'body' => [],
        ];

        if ($message->getStartDate()) {
            $data['header']['startdate'] = $message->getStartDate()->format('dmYHi');
        }

        if ($message->getStopDate()) {
            $data['header']['stopdate'] = $message->getStopDate()->format('dmYHi');
        }

        if ($message->getType() === SmsSendType::ONE_TO_MANY) {
            $data['body']['msg'] = [
                '_cdata' => $message->getMessage(),
            ];

            $data['body']['no'] = [];
            foreach ($message->getNumbers() as $number) {
                $data['body']['no'][] = $number;
            }
        } else {
            $data['body']['mp'] = [];
            foreach ($message->getManyToData() as $value) {
                $data['body']['mp'][] = [
                    'msg' => [
                        '_cdata' => $value['message'],
                    ],
                    'no' => $value['number'],
                ];
            }
        }

        return $data;
    }
}
