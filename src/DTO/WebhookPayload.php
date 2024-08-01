<?php

namespace ComCompany\YousignBundle\DTO;

class WebhookPayload
{
    private string $id;

    private string $eventName;

    private string $status;

    /** @var array<int, array<string, mixed>> */
    private array $members;

    /** @var array<int, array<string, mixed>> */
    private array $files;

    private ?string $workspaceId;

    private ?string $externalId;

    /**
     * @param array<int, array<string, mixed>> $members
     * @param array<int, array<string, mixed>> $files
     */
    public function __construct(
        string $id,
        string $eventName,
        string $status,
        array $members = [],
        array $files = [],
        ?string $workspaceId = null,
        ?string $externalId = null
    ) {
        $this->id = $id;
        $this->eventName = $eventName;
        $this->status = $status;
        $this->members = $members;
        $this->files = $files;
        $this->workspaceId = $workspaceId ?? null;
        $this->externalId = $externalId ?? null;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEventName(): string
    {
        return $this->eventName;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getMembers(): array
    {
        return $this->members;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    public function getWorkspaceId(): ?string
    {
        return $this->workspaceId;
    }

    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
