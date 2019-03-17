<?php

namespace App\Lib;

interface CurrencyApi
{
    public function convert(string $from, string $to): ?float;
}
