<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\DTO\Response;

class SignatureResponse
{
    private const STATUS_DRAFT = 'draft';
    private const STATUS_PENDING = 'pending';
    private const STATUS_ACTIVE = 'active';
    private const STATUS_DONE = 'finished';
    private const STATUS_EXPIRED = 'expired';
    private const STATUS_REFUSED = 'refused';
    private const STATUS_CANCELED = 'canceled';
    private const STATUS_DELETED = 'deleted';

    private string $procedureId;
    private string $status;
    private string $procedureName;
    private string $creationDate;
    private ?string $expirationDate;
    private ?string $workspaceId;

    /** @var DocumentResponse[] */
    private array $documents = [];

    /** @var MemberResponse[] */
    private array $members = [];

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $this->processStatus($status);
    }

    public function getProcedureName(): string
    {
        return $this->procedureName;
    }

    public function setProcedureName(string $procedureName): void
    {
        $this->procedureName = $procedureName;
    }

    public function getCreationDate(): string
    {
        return $this->creationDate;
    }

    public function setCreationDate(string $creationDate): void
    {
        $this->creationDate = $creationDate;
    }

    public function getExpirationDate(): ?string
    {
        return $this->expirationDate;
    }

    public function setExpirationDate(?string $expirationDate): void
    {
        $this->expirationDate = $expirationDate;
    }

    public function getWorkspaceId(): ?string
    {
        return $this->workspaceId;
    }

    public function setWorkspaceId(?string $workspaceId): void
    {
        $this->workspaceId = $workspaceId;
    }

    public function addDocument(DocumentResponse $document): void
    {
        $this->documents[] = $document;
    }

    /** @return DocumentResponse[] */
    public function getDocuments(): array
    {
        return $this->documents;
    }

    public function addMember(MemberResponse $member): void
    {
        $this->members[] = $member;
    }

    /** @return MemberResponse[] */
    public function getMembers(): array
    {
        return $this->members;
    }

    public function setProcedureId(string $procedureId): void
    {
        $this->procedureId = $procedureId;
    }

    public function getProcedureId(): string
    {
        return $this->procedureId;
    }

    public function processStatus(string $status): string
    {
        switch ($status) {
            case 'draft':
                return self::STATUS_DRAFT;
            case 'pending':
                return self::STATUS_PENDING;
            case 'active':
                return self::STATUS_ACTIVE;
            case 'finished':
            case 'completed':
            case 'done':
                return self::STATUS_DONE;
            case 'expired':
                return self::STATUS_EXPIRED;
            case 'refused':
            case 'declined':
            case 'rejected':
                return self::STATUS_REFUSED;
            case 'canceled':
                return self::STATUS_CANCELED;
            case 'deleted':
                return self::STATUS_DELETED;
            default:
                return $status;
        }
    }
}
