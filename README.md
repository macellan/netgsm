# Netgsm SMS Notification Channel for Laravel

![Tests](https://github.com/macellan/netgsm/actions/workflows/tests.yml/badge.svg)
[![Latest Stable Version](https://poser.pugx.org/macellan/netgsm/v/stable)](https://packagist.org/packages/macellan/netgsm)
[![Total Downloads](https://poser.pugx.org/macellan/netgsm/downloads)](https://packagist.org/packages/macellan/netgsm)

This package makes it easy to send sms notifications using [Netgsm](https://www.netgsm.com.tr/dokuman) with Laravel 10.x, 11.x, 12.x

## Contents

- [Installation](#installation)
    - [Setting up the Netgsm service](#setting-up-the-Netgsm-service)
- [Usage](#usage)
    - [ On-Demand Notifications](#on-demand-notifications)
    - [ Usage With Facade](#usage-with-facade)
- [Testing](#testing)
- [Changelog](#changelog)
- [Credits](#credits)

## Installation

You can install this package via composer:

``` bash
composer require macellan/netgsm
```


### Setting up the Netgsm service

Add your Netgsm configs to your config/services.php:

```php
// config/services.php
...
    'sms' => [
        'netgsm' => [
            'username' => env('NETGSM_USERNAME', ''),
            'password' => env('NETGSM_PASSWORD', ''),
            'header' => env('NETGSM_HEADER', ''),
            'language' => env('NETGSM_LANGUAGE', 'tr'),
            'enable' => env('NETGSM_ENABLE', false),
            'debug' => env('NETGSM_DEBUG', false), // Will log sending attempts and results
            'sandbox_mode' => env('NETGSM_SANDBOX_MODE', false), // Will not invoke API call
        ],
    ],
...
```


## Usage

You can use the channel in your via() method inside the notification:

```php
use Illuminate\Notifications\Notification;
use Macellan\Netgsm\DTO\Sms\BaseSmsMessage;
use Macellan\Netgsm\DTO\Sms\SmsMessage;

class TestNotification extends Notification
{
    public function via($notifiable)
    {
        return ['netgsm'];
    }

    public function toNetgsm(object $notifiable): BaseSmsMessage
    {
        return new SmsMessage('Netgsm test message');
    }
}
```

For Otp Sms sending, OtpSmsMessage class can be returned.
```php
return new OtpSmsMessage('Netgsm otp test message');
```

In your notifiable model, make sure to include a routeNotificationForSms() method, which returns a phone number.

```php
public function routeNotificationForSms()
{
    return $this->phone;
}
```


### On-Demand Notifications

Sometimes you may need to send a notification to someone who is not stored as a "user" of your application. Using the Notification::route method, you may specify ad-hoc notification routing information before sending the notification:

```php
Notification::route('sms', '+905554443322')  
            ->notify(new TestNotification());
```

## Usage With Facade
```php
use Macellan\Netgsm\Facades\Netgsm;
use Macellan\Netgsm\DTO\Sms\SmsMessage;
use Macellan\Netgsm\DTO\Sms\OtpSmsMessage;

// Sms send - single sms
$smsMessage = (new SmsMessage('Netgsm test message'))
    ->setNumbers(['+905554443322']);
Netgsm::sendSms($smsMessage);
/**
// return array 
[
    'code' => '00', // Netgsm response code
    'id' => '17377215342605050417149344',  // Job id,
    'description" => 'queued',
]
**/

// Sms send - bulk sms with same message
$smsMessage = (new SmsMessage('Netgsm test message'))
    ->setNumbers(['+905554443322', '+905554443333']);
Netgsm::sendSms($smsMessage);

// Sms send - bulk sms
$smsMessage = (new SmsMessage)
    ->setNumbers(['+905554443322', '+905554443333'])
    ->setMessages(['Message 1', 'Message 2']);
Netgsm::sendSms($smsMessage);

// Otp Sms send
$otpSmsMessage = (new OtpSmsMessage('Netgsm otp test message'))
    ->setNumbers(['+905554443322']);
Netgsm::sendSms($otpSmsMessage);
/**
// return array 
[
    'code' => '00', // Netgsm response code
    'id' => '111111', // Job id
    'error' => '', // Error message
]
**/
```

## Testing

``` bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Credits

- [Arif Demir](https://github.com/epicentre)
