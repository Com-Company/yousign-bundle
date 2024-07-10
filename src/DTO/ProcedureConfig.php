<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\DTO;

class ProcedureConfig
{
    public string $name;

    public string $externalId;

    /** @var array<string, mixed> */
    public array $extraConfig;
    public ?string $workspaceId;

    /** @param array<string, mixed> $extraConfig */
    public function __construct(
        string $name,
        string $externalId,
        array $extraConfig = [],
        ?string $workspaceId = null
    ) {
        $this->name = $name;
        $this->externalId = $externalId;
        $this->extraConfig = $extraConfig;
        $this->workspaceId = $workspaceId;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'external_id' => $this->externalId,
            'delivery_mode' => 'none',
            'workspace_id' => $this->workspaceId,
            'signers_allowed_to_decline' => true,
            'audit_trail_locale' => 'fr',
        ];
    }
}
