<?php

namespace App\Services;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;

class PhoneNumberVerifier
{
    public const SUPPORTED_COUNTRIES = ['NG', 'GB', 'US'];

    public static function isValidNigerianNumber(string $phoneNumber): bool
    {
        return self::verifyPhoneNumber('NG', $phoneNumber);
    }

    public static function isValidUkNumber(string $phoneNumber): bool
    {
        return self::verifyPhoneNumber('GB', $phoneNumber);
    }

    public static function isValidUsNumber(string $phoneNumber): bool
    {
        return self::verifyPhoneNumber('US', $phoneNumber);
    }

    public static function verifyPhoneNumber(string $countryCode, string $phoneNumber): bool
    {
        $region = strtoupper(trim($countryCode));

        /*if (!in_array($region, self::SUPPORTED_COUNTRIES, true)) {
            return false;
        }*/

        if ($phoneNumber === '') {
            return false;
        }

        $phoneNumberUtil = PhoneNumberUtil::getInstance();

        try {
            $parsedNumber = $phoneNumberUtil->parse($phoneNumber, $region);
        } catch (NumberParseException) {
            return false;
        }

        return $phoneNumberUtil->isValidNumberForRegion($parsedNumber, $region);
    }
}

