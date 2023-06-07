<?php

namespace Macellan\Netgsm\DTO\Sms;

use DateTimeInterface;
use Macellan\Netgsm\Enums\SmsSendType;

class SmsMessage extends BaseSmsMessage
{
    protected ?DateTimeInterface $startDate = null;

    protected ?DateTimeInterface $stopDate = null;

    protected SmsSendType $type = SmsSendType::ONE_TO_MANY;

    protected ?int $filter = null;

    protected array $manyToData = [];

    public function __construct(?string $message = null)
    {
        $this->message = $message;
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

    public function getType(): SmsSendType
    {
        return $this->type;
    }

    public function setType(SmsSendType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getFilter(): ?int
    {
        return $this->filter;
    }

    public function setFilter(?int $filter): SmsMessage
    {
        $this->filter = $filter;
        return $this;
    }

    public function getManyToData(): array
    {
        return $this->manyToData;
    }

    public function setManyToData(array $manyToData): self
    {
        $this->manyToData = $manyToData;

        return $this;
    }
}
