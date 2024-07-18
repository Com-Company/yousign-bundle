<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\DTO\Response\Signature;

class DeclineInformation
{
    private ?string $reason;
    private ?string $signerId;
    private ?\DateTime $declinedAt;

    public function __construct(?string $reason = null, ?string $signerId = null, ?\DateTime $declinedAt = null)
    {
        $this->reason = $reason;
        $this->declinedAt = $declinedAt;
        $this->signerId = $signerId;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function getSignerId(): ?string
    {
        return $this->signerId;
    }

    public function getDeclinedAt(): ?\DateTime
    {
        return $this->declinedAt;
    }
}
