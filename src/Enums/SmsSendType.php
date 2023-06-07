<?php

namespace Macellan\Netgsm\Enums;

enum SmsSendType: string
{
    case ONE_TO_MANY = '1:n';

    case MANY_TO_MANY = 'n:n';
}
