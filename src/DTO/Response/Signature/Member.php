<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\DTO\Response\Signature;

class Member
{
    private const INITIATED = 'initiated';
    private const NOTIFIED = 'notified';
    private const VERIFIED = 'verified';
    private const CONSENT_GIVEN = 'consent_given';
    private const PROCESSING = 'processing';
    private const DECLINED = 'declined';
    private const SIGNED = 'signed';
    private const ABORTED = 'aborted';
    private const ERROR = 'error';
    private ?string $id;

    private string $supplierId;

    private string $status;

    private ?string $uri;

    private ?string $comment = null;

    private ?string $firstname;

    private ?string $lastname;

    private ?string $email;

    private ?string $phone;

    public function __construct(
        ?string $id,
        string $supplierId,
        string $status,
        ?string $uri = null,
        ?string $comment = null,
        ?string $firstname = null,
        ?string $lastname = null,
        ?string $email = null,
        ?string $phone = null
    ) {
        $this->id = $id;
        $this->supplierId = $supplierId;
        $this->status = $this->processStatus($status);
        $this->uri = $uri;
        $this->comment = $comment;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->phone = $phone;
        $this->email = $email;
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

    /**
     * Will be removed in V3.
     *
     * @deprecated
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function processStatus(string $status): string
    {
        switch ($status) {
            case 'pending':
                return self::NOTIFIED;
            case 'done':
                return self::SIGNED;
            case 'refused':
                return self::DECLINED;
            default:
                return $status;
        }
    }
}
