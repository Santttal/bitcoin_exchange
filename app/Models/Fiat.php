<?php

namespace App\Models;

class Fiat
{
    const USD = 'USD';
    const EUR = 'EUR';
    const GBP = 'GBP';
    const NGN = 'NGN';

    public static function availableCurrencies()
    {
        return [
            self::USD,
            self::EUR,
            self::GBP,
            self::NGN,
        ];
    }
}
