<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\DTO\Response\Audit;

final class AuditResponse
{
    private Signer $signer;
    private SignatureRequest $signatureRequest;

    public function __construct()
    {
        $this->signer = new Signer();
        $this->signatureRequest = new SignatureRequest();
    }

    public function getSigner(): Signer
    {
        return $this->signer;
    }

    public function getSignatureRequest(): SignatureRequest
    {
        return $this->signatureRequest;
    }

    public function toArray(): array
    {
        return [
            'signer' => $this->signer->toArray(),
            'signatureRequest' => $this->signatureRequest->toArray(),
        ];
    }
}
