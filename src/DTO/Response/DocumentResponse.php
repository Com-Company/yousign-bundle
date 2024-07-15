<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\DTO\Response;

class DocumentResponse
{
    private ?string $id;
    private string $supplierId;
    private string $nature;

    public function __construct(?string $id, string $supplierId, string $nature)
    {
        $this->id = $id;
        $this->supplierId = $supplierId;
        $this->nature = $nature;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getSupplierId(): ?string
    {
        return $this->supplierId;
    }

    public function getNature(): string
    {
        return $this->nature;
    }
}
