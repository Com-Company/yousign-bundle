<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\DTO;

use ComCompany\SignatureContract\DTO\ProcedureConfig as SignatureContractProcedureConfig;

class ProcedureConfig extends SignatureContractProcedureConfig
{
    public string $deliveryMode;

    public ?string $workspaceId;

    public function __construct(
        string $name,
        string $externalId,
        string $deliveryMode = 'none',
        ?string $workspaceId = null
    )
    {
        parent::__construct($name, $externalId);
        $this->deliveryMode = $deliveryMode;
        $this->workspaceId = $workspaceId;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'external_id' => $this->externalId,
            'delivery_mode' => $this->deliveryMode,
            'workspace_id' => $this->workspaceId,
        ];
    }
}