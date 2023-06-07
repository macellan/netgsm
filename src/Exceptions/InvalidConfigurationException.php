<?php

namespace Macellan\Netgsm\Exceptions;

class InvalidConfigurationException extends NetgsmException
{
    public static function configurationNotSet(): self
    {
        return new self('Missing "services.sms.netgsm" setting.');
    }
}
