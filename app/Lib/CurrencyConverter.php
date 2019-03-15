<?php

namespace App\Lib;

use Illuminate\Redis\Connections\Connection;

class CurrencyConverter
{
    /** 5 minutes */
    const EXPIRED_INTERVAL = 300;
    const CURRENCY_VALUE_TEMPLATE = 'fiat:%s:value';
    const CURRENCY_TIME_TEMPLATE = 'fiat:%s:timestamp';

    private $api;

    /**
     * @var Connection
     */
    private $redis;

    public function __construct(\Redis $redis, ExternalCurrencyApi $api)
    {
        $this->redis = $redis::connection();
        $this->api = $api;
    }

    public function toUSD(string $fiat, float $amount): float
    {
        $currency = $this->fetchCurrency($fiat);

        if ($currency === null) {
            return 0.0;
        } else {
            return round($amount / $currency, 2);
        }
    }

    public function fromUSD(string $fiat, float $amount): float
    {
        $exchangeRate = $this->fetchCurrency($fiat);

        if ($exchangeRate === null) {
            return 0.0;
        } else {
            $this->saveToCache($fiat, $exchangeRate);
            return round($amount * $exchangeRate, 2);
        }
    }

    private function isExpired(string $fiat):bool
    {
        $currentTimestamp = time();
        $key = sprintf(self::CURRENCY_TIME_TEMPLATE, $fiat);
        if ($this->redis->exists($key)) {
            $createdAt = (int)$this->redis->get($key);
            return $currentTimestamp - $createdAt > self::EXPIRED_INTERVAL;
        } else {
            return true;
        }
    }

    private function fetchCurrency(string $fiat): ?float
    {
        if ($this->isExpired($fiat)) {
            $currency = $this->fetchFromApi($fiat);
        } else {
            if (!$currency = $this->fetchFromCache($fiat)) {
                $currency = $this->fetchFromApi($fiat);
            }
        }

        return $currency;
    }

    private function fetchFromCache(string $fiat): ?float
    {
        $key = sprintf(self::CURRENCY_VALUE_TEMPLATE, $fiat);
        if ($this->redis->exists($key)) {
            return (float)$this->redis->get($key);
        } else {
            return null;
        }
    }

    private function saveToCache(string $fiat, float $exchangeRate)
    {
        $keyValue = sprintf(self::CURRENCY_VALUE_TEMPLATE, $fiat);
        $keyTemplate = sprintf(self::CURRENCY_TIME_TEMPLATE, $fiat);
        $this->redis->set($keyValue, $exchangeRate);
        $this->redis->set($keyTemplate, time());
    }

    private function fetchFromApi(string $fiat): ?float
    {
        return $this->api->convert(Fiat::USD, $fiat);
    }
}
