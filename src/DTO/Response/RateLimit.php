<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\DTO\Response;

class RateLimit
{
    private ?int $limitHour = null;
    private ?int $remainingHour = null;
    private ?int $limitMinute = null;
    private ?int $remainingMinute = null;

    public function __construct(?int $limitHour, ?int $remainingHour, ?int $limitMinute, ?int $remainingMinute)
    {
        $this->limitHour = $limitHour;
        $this->remainingHour = $remainingHour;
        $this->limitMinute = $limitMinute;
        $this->remainingMinute = $remainingMinute;
    }

    public function getLimitHour(): ?int
    {
        return $this->limitHour;
    }

    public function getRemainingHour(): ?int
    {
        return $this->remainingHour;
    }

    public function getLimitMinute(): ?int
    {
        return $this->limitMinute;
    }

    public function getRemainingMinute(): ?int
    {
        return $this->remainingMinute;
    }

    public function getRateLimitDetail(): string
    {
        return "Hour: {$this->remainingHour}/{$this->limitHour}, Minute: {$this->remainingMinute}/{$this->limitMinute}";
    }

    /** @return array<string, int|null> */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
