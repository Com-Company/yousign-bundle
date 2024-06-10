<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\DTO;

use ComCompany\SignatureContract\DTO\ProcedureConfig as SignatureContractProcedureConfig;

class ProcedureConfig extends SignatureContractProcedureConfig
{
    public string $deliveryMode;

    public string $auditTrailLocale;

    public ?string $workspaceId;

    public bool $signersAllowedToDecline;

    public function __construct(
        string $name,
        string $externalId,
        bool $start = false,
        string $workspaceId = null,
        bool $allowWebhook = true,
    ) {
        parent::__construct($name, $externalId);
        $this->deliveryMode = $deliveryMode;
        $this->workspaceId = $workspaceId;
        $this->signersAllowedToDecline = $signersAllowedToDecline;
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