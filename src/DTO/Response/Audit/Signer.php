<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\DTO\Response\Audit;

final class Signer
{
    private string $id;

    private string $lastname;

    private string $firstname;

    private string $phone;

    private string $email;

    private \DateTime $consentGivenAt;

    private \DateTime $signatureProcessCompleteAt;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getConsentGivenAt(): \DateTime
    {
        return $this->consentGivenAt;
    }

    public function setConsentGivenAt(\DateTime $consentGivenAt): self
    {
        $this->consentGivenAt = $consentGivenAt;

        return $this;
    }

    public function getSignatureProcessCompleteAt(): \DateTime
    {
        return $this->signatureProcessCompleteAt;
    }

    public function setSignatureProcessCompleteAt(\DateTime $signatureProcessCompleteAt): self
    {
        $this->signatureProcessCompleteAt = $signatureProcessCompleteAt;

        return $this;
    }

    /** @return mixed[] */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
