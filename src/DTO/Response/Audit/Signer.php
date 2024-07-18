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

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): void
    {
        $this->lastname = $lastname;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): void
    {
        $this->firstname = $firstname;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getConsentGivenAt(): \DateTime
    {
        return $this->consentGivenAt;
    }

    public function setConsentGivenAt(\DateTime $consentGivenAt): void
    {
        $this->consentGivenAt = $consentGivenAt;
    }

    public function getSignatureProcessCompleteAt(): \DateTime
    {
        return $this->signatureProcessCompleteAt;
    }

    public function setSignatureProcessCompleteAt(\DateTime $signatureProcessCompleteAt): void
    {
        $this->signatureProcessCompleteAt = $signatureProcessCompleteAt;
    }

    /** @return mixed[] */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
