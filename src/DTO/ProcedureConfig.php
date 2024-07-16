<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\DTO;

class ProcedureConfig
{
    public string $name;

    /** @var array<string, mixed> */
    public array $extraConfig;

    public string $workspaceId;

    /** @param array<string, mixed> $extraConfig */
    public function __construct(
        string $name,
        string $workspaceId,
        array $extraConfig = []
    ) {
        $this->name = $name;
        $this->extraConfig = $extraConfig;
        $this->workspaceId = $workspaceId;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_merge(
            [
                'name' => $this->name,
                'workspace_id' => $this->workspaceId,
                'delivery_mode' => 'none',
                'signers_allowed_to_decline' => true,
                'audit_trail_locale' => 'fr',
            ], $this->extraConfig);
    }
}
