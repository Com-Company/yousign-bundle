<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\DTO\Response\Signature;

class SignatureResponse
{
    private const STATUS_DRAFT = 'draft';
    private const STATUS_APPROVAL = 'approval';
    private const STATUS_REJECTED = 'rejected';
    private const STATUS_ONGOING = 'ongoing';
    private const STATUS_DECLINED = 'declined';
    private const STATUS_EXPIRED = 'expired';
    private const STATUS_DELETED = 'deleted';
    private const STATUS_CANCELED = 'canceled';
    private const STATUS_DONE = 'done';

    private string $procedureId;
    private string $status;
    private string $procedureName;
    private \DateTime $creationDate;
    private ?\DateTime $expirationDate;
    private ?string $workspaceId;

    /** @var Document[] */
    private array $documents = [];

    /** @var Member[] */
    private array $members = [];

    private ?DeclineInformation $declineInformation = null;

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $this->processStatus($status);

        return $this;
    }

    public function getProcedureName(): string
    {
        return $this->procedureName;
    }

    public function setProcedureName(string $procedureName): self
    {
        $this->procedureName = $procedureName;

        return $this;
    }

    public function getCreationDate(): \DateTime
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTime $creationDate): self
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    public function getExpirationDate(): ?\DateTime
    {
        return $this->expirationDate;
    }

    public function setExpirationDate(?\DateTime $expirationDate): self
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }

    public function getWorkspaceId(): ?string
    {
        return $this->workspaceId;
    }

    public function setWorkspaceId(?string $workspaceId): self
    {
        $this->workspaceId = $workspaceId;

        return $this;
    }

    public function addDocument(Document $document): self
    {
        $this->documents[] = $document;

        return $this;
    }

    /** @return Document[] */
    public function getDocuments(): array
    {
        return $this->documents;
    }

    public function addMember(Member $member): self
    {
        $this->members[] = $member;

        return $this;
    }

    /** @return Member[] */
    public function getMembers(): array
    {
        return $this->members;
    }

    public function setProcedureId(string $procedureId): self
    {
        $this->procedureId = $procedureId;

        return $this;
    }

    public function getProcedureId(): string
    {
        return $this->procedureId;
    }

    public function getDeclineInformation(): ?DeclineInformation
    {
        return $this->declineInformation;
    }

    public function setDeclineInformation(?DeclineInformation $declineInformation): self
    {
        $this->declineInformation = $declineInformation;

        return $this;
    }

    public function processStatus(string $status): string
    {
        switch ($status) {
            case 'draft':
                return self::STATUS_DRAFT;
            case 'approval':
                return self::STATUS_APPROVAL;
            case 'rejected':
                return self::STATUS_REJECTED;
            case 'pending':
            case 'active':
            case 'ongoing':
                return self::STATUS_ONGOING;
            case 'refused':
            case 'declined':
                return self::STATUS_DECLINED;
            case 'expired':
                return self::STATUS_EXPIRED;
            case 'deleted':
                return self::STATUS_DELETED;
            case 'canceled':
                return self::STATUS_CANCELED;
            case 'finished':
            case 'completed':
            case 'done':
                return self::STATUS_DONE;
            default:
                return $status;
        }
    }
}
