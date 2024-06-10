<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\DTO;

use ComCompany\SignatureContract\DTO\MemberConfig as MemberConfigBase;

class MemberConfig extends MemberConfigBase
{
    public function __construct(string $signatureLevel, ?string $signatureAuthentificationMode = null)
    {
        parent::__construct($signatureLevel, $signatureAuthentificationMode);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
