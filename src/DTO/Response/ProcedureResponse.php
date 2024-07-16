<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\DTO\Response;

class ProcedureResponse
{
    private string $id;

    private string $status;

    private string $expirationDate;

    public function __construct(string $id, string $status, string $expirationDate)
    {
        $this->id = $id;
        $this->status = $status;
        $this->expirationDate = $expirationDate;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function getExpirationDate(): ?string
    {
        return $this->expirationDate;
    }
}
