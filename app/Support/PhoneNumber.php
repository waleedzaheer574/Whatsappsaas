<?php

namespace App\Support;

class PhoneNumber
{
    public static function normalize(string $phone, string $countryCode = '+92'): string
    {
        $phone = trim($phone);
        $countryDigits = preg_replace('/\D+/', '', $countryCode) ?: '92';

        if ($phone === '') {
            return '+'.$countryDigits;
        }

        $digits = preg_replace('/\D+/', '', $phone);

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (! str_starts_with($digits, $countryDigits)) {
            $digits = $countryDigits.ltrim($digits, '0');
        }

        if (str_starts_with($digits, $countryDigits.'0')) {
            $digits = $countryDigits.substr($digits, strlen($countryDigits) + 1);
        }

        return '+'.$digits;
    }

    public static function e164Digits(string $phone, string $countryCode = '+92'): string
    {
        return preg_replace('/\D+/', '', self::normalize($phone, $countryCode));
    }
}
