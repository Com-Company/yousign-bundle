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

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSentAt(): \DateTime
    {
        return $this->sentAt;
    }

    public function setSentAt(\DateTime $sentAt): self
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    public function getExpiredAt(): \DateTime
    {
        return $this->expiredAt;
    }

    public function setExpiredAt(\DateTime $expiredAt): self
    {
        $this->expiredAt = $expiredAt;

        return $this;
    }

    /** @return mixed[] */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
