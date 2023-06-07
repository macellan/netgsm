<?php

namespace Macellan\Netgsm\Tests;

use Exception;
use Macellan\Netgsm\Exceptions\NetgsmException;
use Macellan\Netgsm\Netgsm;
use Macellan\Netgsm\NetgsmChannel;
use Macellan\Netgsm\Tests\Notifications\TestNotifiable;
use Macellan\Netgsm\Tests\Notifications\TestSmsNotification;
use Mockery;

class NetgsmChannelTest extends TestCase
{
    private array $config;

    public function setUp(): void
    {
        parent::setUp();

        $this->config = $this->app['config']->get('services.sms.netgsm');
    }

    public function test_send_notification(): void
    {
        $this->config['enable'] = true;
        $this->config['debug'] = true;
        $this->config['sandbox_mode'] = false;

        $netgsm = Mockery::mock(Netgsm::class, [$this->config])->makePartial();
        $netgsm
            ->shouldReceive('sendSms')
            ->andReturn([]);

        $channel = new NetgsmChannel($netgsm);

        $channel->send(new TestNotifiable(), new TestSmsNotification());

        $netgsm->shouldHaveReceived('sendSms');
    }

    public function test_can_not_send_notification_with_disable(): void
    {
        $this->config['enable'] = false;

        $netgsm = Mockery::mock(Netgsm::class, [$this->config])->makePartial();

        $channel = new NetgsmChannel($netgsm);

        $notification = Mockery::mock(TestSmsNotification::class);

        $channel->send(new TestNotifiable(), $notification);

        $notification->shouldNotHaveReceived('toNetgsm');
    }

    public function test_can_not_send_notification_invalid_message(): void
    {
        $this->config['enable'] = true;
        $this->config['sandbox_mode'] = false;

        $this->expectException(NetgsmException::class);

        $netgsm = Mockery::mock(Netgsm::class, [$this->config])->makePartial();

        $channel = new NetgsmChannel($netgsm);

        $notification = Mockery::mock(TestSmsNotification::class)->makePartial();
        $notification
            ->shouldReceive('toNetgsm')
            ->andReturn(null);

        $channel->send(new TestNotifiable(), $notification);
        $netgsm->shouldNotHaveReceived('sendSms');
    }

    public function test_can_not_send_notification_with_sandbox_mode(): void
    {
        $this->config['enable'] = true;
        $this->config['sandbox_mode'] = true;

        $netgsm = Mockery::mock(Netgsm::class, [$this->config])->makePartial();

        $channel = new NetgsmChannel($netgsm);

        $channel->send(new TestNotifiable(), new TestSmsNotification());

        $netgsm->shouldNotHaveReceived('sendSms');
    }

    public function test_can_not_send_notification_throw_exception(): void
    {
        $this->config['enable'] = true;
        $this->config['debug'] = true;
        $this->config['sandbox_mode'] = false;

        $this->expectException(Exception::class);

        $netgsm = Mockery::mock(Netgsm::class, [$this->config])->makePartial();
        $netgsm
            ->shouldReceive('sendSms')
            ->andThrow(new Exception());

        $channel = new NetgsmChannel($netgsm);

        $channel->send(new TestNotifiable(), new TestSmsNotification());
    }
}
