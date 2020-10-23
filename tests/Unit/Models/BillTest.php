<?php

namespace Tests\Unit\Models;

use App\Models\Bill;
use App\Models\Cart;
use App\Models\Currency;
use App\Models\Product;
use Tests\TestCase;

class BillTest extends TestCase
{
    private $currency;
    private $cart;
    private $products;
    private $bill;

    /**
     * @test
     */
    public function it_contains_products()
    {
        // Arrange
        $this->setupTest(null,null,);

        // Assert
        $this->assertInstanceOf(Product::class, $this->bill->getItems()->get(0));
    }

    /**
     * @test
     */
    public function it_can_convert_currency()
    {
        // Arrange
        $this->setupTest(null, new Currency("egp", "e£", $exchange_rate = Currency::USD_EXCHANGE_RATES['egp']));
        $usd_prices = collect($this->bill->getItems()->pluck('price'));

        // Act
        $this->bill->convertCurrency();
        $egp_prices = collect($this->bill->getItems()->pluck('price'));

        // Assert
        $this->assertEquals($usd_prices->count(), $egp_prices->count());
        foreach (range(0, $usd_prices->count()) as $i) {
            $this->assertEquals($usd_prices->get($i), $egp_prices->get($i) / $exchange_rate);
        }
    }

    /**
     * @test
     */
    public function it_can_apply_tax()
    {
        // Arrange
        $this->setupTest(null,null,);
        $this->assertEquals(0, $this->bill->getTax());

        // Act
        $this->bill->applyTax();

        // Assert
        $this->assertNotEquals(0, $this->bill->getTax());
        $this->assertGreaterThan($this->bill->getSubTotal(), $this->bill->getTotal());
        $this->assertEquals( $this->bill->getTotal(), $this->bill->getSubTotal() + $this->bill->getTax());
    }

    /**
     * @test
     */
    public function it_can_apply_bundle_offers()
    {
        // Arrange
        // jacket price is 19.99 -> should be 9.995
        $products = ['t-shirt', 't-shirt', 'jacket'];
        $cart = new Cart;
        $cart->collectValidProducts($products);
        $this->setupTest($cart,null,);
        $this->assertCount(0, $this->bill->getDiscounts());

        // Act
        $this->bill->applyBundleOffers();

        // Assert
        $this->assertCount(1, $this->bill->getDiscounts());
        $this->assertArrayHasKey('discount_value', $this->bill->getDiscounts()[0]);
        $this->assertEquals(9.995,  $this->bill->getDiscounts()[0]['discount_value']);
    }

    /**
     * @test
     */
    public function it_can_apply_solo_offers()
    {
        // Arrange
        // shoes is 24.99 and has 10& discount -> discount should be 2.499
        $products = ['t-shirt', 'shoes', 'jacket'];
        $cart = new Cart;
        $cart->collectValidProducts($products);
        $this->setupTest($cart,null,);
        $this->assertCount(0, $this->bill->getDiscounts());

        // Act
        $this->bill->applySoloOffers();

        // Assert
        $this->assertCount(1, $this->bill->getDiscounts());
        $this->assertArrayHasKey('discount_value', $this->bill->getDiscounts()[0]);
        $this->assertEquals(2.499,  $this->bill->getDiscounts()[0]['discount_value']);
    }

    /**
     * @test
     */
    public function it_can_apply_multiple_offers()
    {
        // Arrange
        // shoes is 24.99 and has 10& discount -> discount should be 2.499
        // shoes is 24.99 and has 10& discount -> discount should be 2.499
        $products = ['t-shirt', 'shoes', 'jacket', 't-shirt'];
        $cart = new Cart;
        $cart->collectValidProducts($products);
        $this->setupTest($cart,null,);
        $this->assertCount(0, $this->bill->getDiscounts());

        // Act
        $this->bill->applyOffers();

        // Assert
        $this->assertCount(2, $this->bill->getDiscounts());
        $this->assertArrayHasKey('discount_value', $this->bill->getDiscounts()[0]);
        $this->assertArrayHasKey('discount_value', $this->bill->getDiscounts()[1]);
        $this->assertEquals(9.995,  $this->bill->getDiscounts()[0]['discount_value']);
        $this->assertEquals(2.499,  $this->bill->getDiscounts()[1]['discount_value']);
    }

    /**
     * @test
     */
    public function it_will_not_apply_bundle_offer_when_conditions_are_not_me()
    {
        // Arrange
        // jacket price is 19.99 -> will remain 19.99 because we bought only 1 shirt
        $products = ['t-shirt', 'jacket'];
        $cart = new Cart;
        $cart->collectValidProducts($products);
        $this->setupTest($cart,null,);

        // Act
        $this->bill->applyBundleOffers();

        // Assert
        $this->assertCount(0, $this->bill->getDiscounts());
    }

    private function setupTest(Cart $cart = null, Currency $currency = null)
    {
        $this->cart = $cart;

        if($this->cart == null) {
            $this->cart = new Cart;
            $this->products = ['t-shirt', 't-shirt', 'shoes', 'jacket'];
            $this->cart->collectValidProducts($this->products);
        }

        $this->currency = $currency ?? new Currency("usd", "$", 1);

        $this->bill = new Bill($this->cart, $this->currency);
    }
}
