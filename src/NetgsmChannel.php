<?php

namespace Macellan\Netgsm;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Macellan\Netgsm\DTO\Sms\BaseSmsMessage;
use Macellan\Netgsm\Exceptions\NetgsmException;
use Throwable;

class NetgsmChannel
{
    protected Netgsm $netgsm;

    /**
     * If true, will run.
     */
    private bool $enable;

    /**
     * Debug flag. If true, messages send/result wil be stored in Laravel log.
     */
    private bool $debug;

    /**
     * Sandbox mode flag. If true, endpoint API will not be invoked, useful for dev purposes.
     */
    private bool $sandboxMode;

    public function __construct(Netgsm $netgsm)
    {
        $this->netgsm = $netgsm;

        $this->enable = Arr::get($this->netgsm->getConfig(), 'enable', false);
        $this->debug = Arr::get($this->netgsm->getConfig(), 'debug', false);
        $this->sandboxMode = Arr::get($this->netgsm->getConfig(), 'sandbox_mode', true);
    }

    /**
     * Send the given notification.
     *
     * @throws NetgsmException|Throwable
     */
    public function send($notifiable, Notification $notification): void
    {
        if (! $this->enable) {
            $this->log('Netgsm is disabled');

            return;
        }

        /** @phpstan-ignore method.notFound */
        $message = $notification->toNetgsm($notifiable);

        if (! $message instanceof BaseSmsMessage) {
            throw new NetgsmException(trans('netgsm::errors.invalid_netgsm_message'));
        }

        if (! $message->getNumbers()) {
            $phone = $notifiable->routeNotificationFor('sms');
            $message->setNumbers(Arr::wrap($phone));
        }

        $this->log(
            sprintf(
                'Netgsm sending sms - Message: %s - Numbers: %s',
                $message->getMessage(),
                implode(',', $message->getNumbers())
            )
        );

        if ($this->sandboxMode) {
            return;
        }

        try {
            $response = $this->netgsm->sendSms($message);

            $this->log('Netgsm sms send response - '.print_r($response, true));
        } catch (Throwable $e) {
            $this->log(sprintf(
                'Sms message could not be sent. Error: %s',
                $e->getMessage()
            ));

            throw $e;
        }
    }

    private function log(string $message, string $level = 'info', array $context = []): void
    {
        if ($this->debug) {
            Log::log($level, $message, $context);
        }
    }
}
