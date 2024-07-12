<?php

declare(strict_types=1);

namespace ComCompany\YousignBundle\DTO;

class MemberConfig
{
    /**
     * contralia: 'signatureLevel', required [SIMPLE_LCP, ADVANCED_LCP, ADVANCED_NCP, ADVANCED_QCP, QUALIFIED ].
     *
     * yousign: 'signature_level', required [electronic_signature, advanced_electronic_signature, qualified_electronic_signature]
     */
    public string $signatureLevel;

    /**
     * contralia: 'signatureType', optional; default = OTP [OTP, PAD, TOKEN, CONSENT_PROOF, CONSENT beta ou IDENTITY].
     *
     * yousign: 'signature_authentication_mode', optional; default = null [otp_email, otp_sms, no_otp]
     */
    public ?string $signatureAuthentificationMode = null;

    public function __construct(string $signatureLevel, ?string $signatureAuthentificationMode = null)
    {
        $this->signatureLevel = $signatureLevel;
        $this->signatureAuthentificationMode = $signatureAuthentificationMode;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
