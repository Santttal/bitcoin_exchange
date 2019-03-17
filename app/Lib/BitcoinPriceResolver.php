<?php

namespace App\Lib;

use App\Models\Fiat;

class BitcoinPriceResolver
{
    const CURRENT_PRICE = 3000.0;

    /**
     * @var CurrencyConverter
     */
    private $converter;

    /**
     * @param CurrencyConverter $converter
     */
    public function __construct(CurrencyConverter $converter)
    {
        $this->converter = $converter;
    }

    public function getPrice(string $fiat): float
    {
        if ($fiat === Fiat::USD) {
            return self::CURRENT_PRICE;
        }

        return $this->converter->fromUSD($fiat, self::CURRENT_PRICE);
    }
}
