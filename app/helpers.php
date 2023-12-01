<?php

use Illuminate\Database\Schema\Builder;

/**
 * Get the validation rule for maximum string length based on the default database string length.
 *
 * @return string
 */
function maxString()
{
    return 'max:' . Builder::$defaultStringLength;
}

/**
 * Generates a random six-digit verification code.
 *
 * @return string Random six-digit code.
 */
function verificationCode()
{
    // Pad the random integer with leading zeros to ensure it has six digits
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}
