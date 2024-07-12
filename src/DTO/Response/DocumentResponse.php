<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\DTO\Response;

class DocumentResponse
{
    private ?string $id;
    private string $supplierId;
    private string $type;

    public function __construct(?string $id, string $supplierId, string $type)
    {
        $this->id = $id;
        $this->supplierId = $supplierId;
        $this->type = $type;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getSupplierId(): ?string
    {
        return $this->supplierId;
    }

    public function getType(): ?string
    {
        return $this->type;
    }
}
