<?php

namespace Macellan\Netgsm\DTO\Sms;

use DateTimeInterface;

class SmsMessage extends BaseSmsMessage
{
    protected string $encoding = 'TR';

    protected string $iysFilter = "0";

    protected ?string $partnerCode = null;

    protected ?string $appName = null;

    protected ?DateTimeInterface $startDate = null;

    protected ?DateTimeInterface $stopDate = null;

    protected ?array $messages = null;

    public function __construct(?string $message = null)
    {
        $this->message = $message;
    }

    public function getEncoding(): string
    {
        return $this->encoding;
    }

    public function setEncoding(string $encoding): self
    {
        $this->encoding = $encoding;

        return $this;
    }

    public function getIysFilter(): string
    {
        return $this->iysFilter;
    }

    public function setIysFilter(string $iysFilter): self
    {
        $this->iysFilter = $iysFilter;

        return $this;
    }

    public function getPartnerCode(): ?string
    {
        return $this->partnerCode;
    }

    public function setPartnerCode(?string $partnerCode): self
    {
        $this->partnerCode = $partnerCode;

        return $this;
    }

    public function getAppName(): ?string
    {
        return $this->appName;
    }

    public function setAppName(?string $appName): self
    {
        $this->appName = $appName;

        return $this;
    }

    public function getStartDate(): ?DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getStopDate(): ?DateTimeInterface
    {
        return $this->stopDate;
    }

    public function setStopDate(?DateTimeInterface $stopDate): self
    {
        $this->stopDate = $stopDate;

        return $this;
    }

    public function getMessages(): ?array
    {
        return $this->messages;
    }

    public function setMessages(?array $messages): self
    {
        $this->messages = $messages;

        return $this;
    }
}
