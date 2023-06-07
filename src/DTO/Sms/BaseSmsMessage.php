<?php

namespace Macellan\Netgsm\DTO\Sms;

abstract class BaseSmsMessage
{
    protected ?string $header = null;

    protected ?string $message = null;

    protected array $numbers = [];

    public function getHeader(): ?string
    {
        return $this->header;
    }

    public function setHeader(?string $header): self
    {
        $this->header = $header;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getNumbers(): array
    {
        return $this->numbers;
    }

    public function setNumbers(array $numbers): self
    {
        $this->numbers = $numbers;

        return $this;
    }
}
