<?php

namespace Macellan\Netgsm\Tests;

use Illuminate\Contracts\Config\Repository;
use Macellan\Netgsm\NetgsmServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string<\Illuminate\Support\ServiceProvider>>
     */
    protected function getPackageProviders($app)
    {
        return [
            NetgsmServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function defineEnvironment($app)
    {
        tap($app->make('config'), function (Repository $config) {
            $config->set('services.sms.netgsm', [
                'username' => 'TEST',
                'password' => 'TEST',
                'header' => 'TEST',
                'language' => 'tr',
                'enable' => true,
                'debug' => true,
                'sandbox_mode' => false,
            ]);
        });
    }
}
