<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\DTO\Response\Signature;

class Member
{
    private ?string $id;
    private string $supplierId;
    private string $status;
    private ?string $uri;

    public function __construct(?string $id, string $supplierId, string $status, ?string $uri = null)
    {
        $this->id = $id;
        $this->supplierId = $supplierId;
        $this->status = $status;
        $this->uri = $uri;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getSupplierId(): ?string
    {
        return $this->supplierId;
    }

    public function getUri(): ?string
    {
        return $this->uri;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }
}
