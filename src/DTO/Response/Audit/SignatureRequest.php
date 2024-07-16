<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\DTO\Response\Audit;

final class SignatureRequest
{
    private string $id;
    private string $name;
    private string $sentAt;
    private string $expiredAt;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getSentAt(): string
    {
        return $this->sentAt;
    }

    public function setSentAt(string $sentAt): void
    {
        $this->sentAt = $sentAt;
    }

    public function getExpiredAt(): string
    {
        return $this->expiredAt;
    }

    public function setExpiredAt(string $expiredAt): void
    {
        $this->expiredAt = $expiredAt;
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
