<?php

namespace Tests\Unit\Models;

use App\Models\Currency;
use Tests\TestCase;

class CurrencyTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_extract_valid_currency()
    {
        // Arrange
        $currency = null;
        $potential_currency = 'EGP';

        // Act
        $currency = Currency::getCurrency($potential_currency);

        // Assert
        $this->assertNotNull($currency);
        $this->assertInstanceOf(Currency::class, $currency);
    }

    /**
     * @test
     */
    public function it_returns_null_if_given_invalid_currency()
    {
        // Arrange
        $currency = null;
        $potential_currency = 'SAR';

        // Act
        $currency = Currency::getCurrency($potential_currency);

        // Assert
        $this->assertNull($currency);
    }

    /**
     * @test
     */
    public function it_can_extract_valid_currency_while_being_case_insensitive()
    {
        // Arrange
        $currency = null;
        $potential_currency = 'eGp';

        // Act
        $currency = Currency::getCurrency($potential_currency);

        // Assert
        $this->assertNotNull($currency);
        $this->assertInstanceOf(Currency::class, $currency);
    }

    /**
     * @test
     */
    public function it_has_exchange_rate_values_for_valid_currencies()
    {
        // Arrange
        $valid_currencies = array_keys(Currency::VALID_CURRENCIES);

        // Assert
        foreach ($valid_currencies as $currency) {
            $this->assertNotNull(Currency::USD_EXCHANGE_RATES[$currency]);
        }
    }
}
