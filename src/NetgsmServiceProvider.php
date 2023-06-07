<?php

namespace Macellan\Netgsm;

use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;
use Macellan\Netgsm\Exceptions\InvalidConfigurationException;

class NetgsmServiceProvider extends ServiceProvider
{
    public function register()
    {
    }

    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'netgsm');

        $this->app->singleton(Netgsm::class, function () {
            $config = config('services.sms.netgsm');

            if (empty($config)) {
                throw InvalidConfigurationException::configurationNotSet();
            }

            return new Netgsm($config);
        });

        Notification::resolved(function (ChannelManager $service) {
            $service->extend('netgsm', function () {
                return new NetgsmChannel($this->app->make(Netgsm::class));
            });
        });
    }
}
