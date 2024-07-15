<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\DTO;

use ComCompany\YousignBundle\Constants\SignatureAuthenticationMode;
use ComCompany\YousignBundle\Constants\SignatureLevels;
use ComCompany\YousignBundle\Exception\ClientException;

class MemberConfig
{
    public string $signatureLevel;

    public string $signatureAuthenticationMode;

    public function __construct(string $signatureLevel, ?string $signatureAuthenticationMode = null)
    {
        if (!in_array($signatureLevel, SignatureLevels::SIGNATURE_LEVELS, true)) {
            throw new ClientException('Invalid signature level', 400);
        }

        if (!in_array($signatureAuthenticationMode, SignatureAuthenticationMode::AUTHENTIFICATION_MODE, true)) {
            throw new ClientException('Invalid Authentication mode', 400);
        }
        $this->signatureLevel = $signatureLevel;
        $this->signatureAuthenticationMode = $signatureAuthenticationMode;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
