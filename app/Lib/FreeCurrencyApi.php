<?php

namespace App\Lib;

use GuzzleHttp\Client;

class FreeCurrencyApi implements CurrencyApi
{
    const CONVERT_URI = '/api/v6/convert?q=%s_%s&compact=ultra&apiKey=%s';
    /**
     * @var Client
     */
    private $client;
    /**
     * @var string
     */
    private $apiKey;

    public function __construct(string $apiKey, string $baseUrl)
    {
        $this->client = new Client(['base_uri' => $baseUrl]);
        $this->apiKey = $apiKey;
    }

    public function convert(string $from, string $to): ?float
    {
        try {
            $response = $this->client->get(sprintf(self::CONVERT_URI, $from, $to, $this->apiKey));
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
