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
        string $deliveryMode = 'none',
        bool $signersAllowedToDecline = false,
        string $auditTrailLocale = 'fr',
        string $workspaceId = null
    ) {
        parent::__construct($name, $externalId);
        $this->deliveryMode = $deliveryMode;
        $this->workspaceId = $workspaceId;
        $this->signersAllowedToDecline = $signersAllowedToDecline;
        $this->auditTrailLocale = $auditTrailLocale;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'external_id' => $this->externalId,
            'delivery_mode' => $this->deliveryMode,
            'workspace_id' => $this->workspaceId,
            'signers_allowed_to_decline' => $this->signersAllowedToDecline,
            'audit_trail_locale' => $this->auditTrailLocale,
        ];
    }
}