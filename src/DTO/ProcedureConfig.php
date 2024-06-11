<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\DTO;

use ComCompany\SignatureContract\DTO\ProcedureConfig as SignatureContractProcedureConfig;

class ProcedureConfig extends SignatureContractProcedureConfig
{
    public ?string $workspaceId;

    public function __construct(
        string $name,
        string $externalId,
        ?string $workspaceId = null
    ) {
        parent::__construct($name, $externalId);
        $this->workspaceId = $workspaceId;
    }

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
