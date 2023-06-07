<?php

namespace Macellan\Netgsm\Facades;

use Illuminate\Support\Facades\Facade;
use Macellan\Netgsm\Netgsm as NetgsmClient;

class Netgsm extends Facade
{
    protected static function getFacadeAccessor()
    {
        return NetgsmClient::class;
    }
}
