<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\DTO\Response;

class DocumentResponse
{
    private string $id;
    private int $totalPages;

    private string $createdAt;

    public function __construct(string $id, int $totalPages, string $createdAt)
    {
        $this->id = $id;
        $this->totalPages = $totalPages;
        $this->createdAt = $createdAt;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }
}
