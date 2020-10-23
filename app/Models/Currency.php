<?php


namespace App\Models;


class Currency
{
    public string $code;
    public string $symbol;
    public float $exchange_rate;

    /**
     * Currency constructor.
     * @param string $code
     * @param string $symbol
     * @param float $exchange_rate
     */
    public function __construct(string $code, string $symbol, float $exchange_rate)
    {
        $this->code = $code;
        $this->symbol = $symbol;
        $this->exchange_rate = $exchange_rate;
    }

    /**
     *
     */
    CONST VALID_CURRENCIES = [
        'usd' => [
            'symbol' => '$',
            'direction' => 'ltr'
        ],
        'egp' => [
            'symbol' => 'e£',
            'direction' => 'rtl'
        ]
    ];

    /**
     *
     */
    CONST USD_EXCHANGE_RATES = [
        'usd' => 1,
        'egp' => 15.7
    ];

    public static function getCurrency(string $potential_currency_code)
    {
        if(!isset(self::VALID_CURRENCIES[strtolower($potential_currency_code)])) {
            return null;
        }

        if(!isset(self::USD_EXCHANGE_RATES[strtolower($potential_currency_code)])) {
            return null;
        }

        return new Currency(
            strtolower($potential_currency_code),
            self::VALID_CURRENCIES[strtolower($potential_currency_code)]['symbol'],
            self::USD_EXCHANGE_RATES[strtolower($potential_currency_code)]
        );
    }

    public function display($value)
    {
        if(self::VALID_CURRENCIES[$this->code]['direction'] == 'ltr') {
            return "{$this->symbol}{$value}";
        }

        return "{$value} {$this->symbol}";
    }
}
