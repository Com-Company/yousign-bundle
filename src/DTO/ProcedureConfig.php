<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\DTO;

class ProcedureConfig
{
    public string $name;

    /** @param array<string, mixed> $extraConfig */
    public array $extraConfig;

    public string $workspaceId;

    public string $deliveryMode;

    public function __construct(
        string $name,
        string $workspaceId,
        string $deliveryMode = 'none',
        array $extraConfig = []
    ) {
        $this->name = $name;
        $this->extraConfig = $extraConfig;
        $this->workspaceId = $workspaceId;
        $this->deliveryMode = $deliveryMode;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_merge(
            [
                'name' => $this->name,
                'workspace_id' => $this->workspaceId,
                'delivery_mode' => $this->deliveryMode,
                'signers_allowed_to_decline' => true,
                'audit_trail_locale' => 'fr',
            ], $this->extraConfig);
    }
}
