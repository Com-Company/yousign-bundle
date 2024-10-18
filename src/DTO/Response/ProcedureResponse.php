<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\DTO\Response;

class ProcedureResponse
{
    private string $id;

    private string $status;

    private \DateTime $expirationDate;

    private ?RateLimit $rateLimit = null;

    public function __construct(string $id, string $status, \DateTime $expirationDate, ?RateLimit $rateLimit = null)
    {
        $this->id = $id;
        $this->status = $status;
        $this->expirationDate = $expirationDate;
        $this->rateLimit = $rateLimit;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function getExpirationDate(): ?\DateTime
    {
        return $this->expirationDate;
    }

    public function getRateLimit(): ?RateLimit
    {
        return $this->rateLimit;
    }
}
