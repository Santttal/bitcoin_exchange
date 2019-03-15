<?php

namespace App\Lib;

use GuzzleHttp\Client;

class ExternalCurrencyApi
{
    const BASE_URL = 'http://free.currencyconverterapi.com';

    const API_KEY = 'c364d7a1b8273ef6bd88';

    const CONVERT_URI = '/api/v6/convert?q=%s_%s&compact=ultra&apiKey=%s';

    private $client;

    public function __construct()
    {
        $this->client = new Client(['base_uri' => self::BASE_URL]);
    }

    public function convert(string $from, string $to): ?float
    {
        try {
            $response = $this->client->get(sprintf(self::CONVERT_URI, $from, $to, self::API_KEY));
            $responseArray = json_decode($response->getBody()->getContents(), true);
            $currStr = "{$from}_{$to}";
            if (array_key_exists($currStr, $responseArray)) {
                $currency = (float)$responseArray[$currStr];
            } else {
                $currency = null;
            }
        } catch (\Exception $e) {
            $currency = null;
        }

        return $currency;
    }
}
