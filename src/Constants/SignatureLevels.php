<?php

namespace ComCompany\YousignBundle\Constants;

final class SignatureLevels
{
    public const ELECTRONIC_SIGNATURE = 'electronic_signature';
    public const ADVANCED_ELECTRONIC_SIGNATURE = 'advanced_electronic_signature';

    public const SIGNATURE_LEVELS = [
        self::ELECTRONIC_SIGNATURE,
        self::ADVANCED_ELECTRONIC_SIGNATURE,
    ];
}
