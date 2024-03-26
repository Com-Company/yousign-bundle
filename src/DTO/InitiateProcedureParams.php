<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\DTO;

use ComCompany\SignatureContract\DTO\InitiateProcedure;

class InitiateProcedureParams extends InitiateProcedure
{
    public string $deliveryMode;

    public function __construct(string $name, string $externalId, string $deliveryMode = 'none')
    {
        parent::__construct($name, $externalId);
        $this->deliveryMode = $deliveryMode;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'external_id' => $this->externalId,
            'delivery_mode' => $this->deliveryMode,
        ];
    }
}