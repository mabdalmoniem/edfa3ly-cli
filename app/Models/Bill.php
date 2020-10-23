<?php

namespace App\Models;

use Illuminate\Support\Collection;

class Bill
{
    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var Currency
     */
    protected $currency;

    /**
     * @var Collection
     */
    protected $items;

    /**
     * @var
     */
    protected $subtotal;

    /**
     * @var
     */
    protected $total;

    /**
     * @var
     */
    protected $tax;

    /**
     * @var array
     */
    protected $discounts = [];

    const FIXED_TAX = 0.14;

    /**
     * The current available offers
     */
    const CURRENT_OFFERS = [
        'shoes' => [
            'name' => 'shoes',
            'type' => 'solo_discount',
            'value' => 0.1
        ],
        'jacket' => [
            'name' => 'jacket',
            'type' => 'bundle_discount',
            'value' => 0.5,
            'bundled_with' => 't-shirt',
            'bundle_count' => 2
        ]
    ];

    public function __construct(Cart $cart, Currency $currency)
    {
        $this->cart = $cart;
        $this->currency = $currency;
        $this->items = $this->cart->getProducts();
    }

    public function convertCurrency()
    {
        $this->items = $this->items->map(function ($product) {
            $product->price *= $this->currency->exchange_rate;
            return $product;
        });

        return $this;
    }

    public function applyTax()
    {
        $this->subtotal = $this->items->sum('price');
        $this->tax = $this->subtotal * self::FIXED_TAX;
        $this->total = $this->subtotal + $this->tax;

        return $this;
    }

    public function applyOffers()
    {
        $this->applyBundleOffers();

        $this->applySoloOffers();

        $this->total -= collect($this->discounts)->sum('discount_value');

        return $this;
    }

    /**
     * @return float
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * @return Cart
     */
    public function getCart(): Cart
    {
        return $this->cart;
    }

    /**
     * @return Currency
     */
    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    /**
     * @return Collection
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    /**
     * @return mixed
     */
    public function getSubtotal()
    {
        return $this->subtotal;
    }

    /**
     * @return mixed
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @return array
     */
    public function getDiscounts(): array
    {
        return $this->discounts;
    }

    public function applyBundleOffers(): void
    {
        $this->items = $this->items->map(function ($product) {
            if (!isset(self::CURRENT_OFFERS[$product->name])) return $product;

            $offer = self::CURRENT_OFFERS[$product->name];

            $products = $this->cart->collectProductsAvailableForBundling($offer);

            if ($offer['type'] == 'bundle_discount' && $products->count() == $offer['bundle_count']) {

                $discount_value = $product->price - ($product->price * (1 - $offer['value']));
                $this->discounts[] = [
                    'discount_value' => $discount_value,
                    'message' => $offer['value'] * 100 . "% off " . $offer['name'] . ": -" . $this->currency->display($discount_value)
                ];

                $product->applyDiscount($offer['value'], true);
                $this->cart->markProductsAsUsedForBundling($products);
            }

            return $product;
        });
    }

    public function applySoloOffers(): void
    {
        $this->items = $this->items->map(function ($product) {
            if (isset(self::CURRENT_OFFERS[$product->name]) && self::CURRENT_OFFERS[$product->name]['type'] == 'solo_discount') {

                $discount_value = $product->price - ($product->price * (1 - self::CURRENT_OFFERS[$product->name]['value']));
                $this->discounts[] = [
                    'discount_value' => $discount_value,
                    'message' => self::CURRENT_OFFERS[$product->name]['value'] * 100
                        . "% off " . $product->name . ": -" . $this->currency->display($discount_value)
                ];

                $product->applyDiscount(self::CURRENT_OFFERS[$product->name]['value']);
            }
            return $product;
        });
    }
}
