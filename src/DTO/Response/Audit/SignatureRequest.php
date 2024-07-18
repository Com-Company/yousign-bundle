<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\DTO\Response\Audit;

final class SignatureRequest
{
    private string $id;
    private string $name;
    private \DateTime $sentAt;
    private \DateTime $expiredAt;

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

    public function getSentAt(): \DateTime
    {
        return $this->sentAt;
    }

    public function setSentAt(\DateTime $sentAt): void
    {
        $this->sentAt = $sentAt;
    }

    public function getExpiredAt(): \DateTime
    {
        return $this->expiredAt;
    }

    public function setExpiredAt(\DateTime $expiredAt): void
    {
        $this->expiredAt = $expiredAt;
    }

    /** @return mixed[] */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
