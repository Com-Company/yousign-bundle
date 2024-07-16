<?php

namespace ComCompany\YousignBundle\Constants;

final class SignatureAuthenticationMode
{
    public const OTP_EMAIL = 'otp_email';
    public const OTP_SMS = 'otp_sms';
    public const NO_OTP = 'no_otp';

    public const AUTHENTICATION_MODE = [
        self::OTP_EMAIL,
        self::OTP_SMS,
        self::NO_OTP,
    ];
}
