<?php


namespace App\Models;


use Illuminate\Support\Collection;

class Cart
{
    protected $products;
    /**
     *
     */
    CONST VALID_PRODUCTS = [
        't-shirt' => [
            'price' => 10.99,
            'discount' => 0
        ],
        'pants' => [
            'price' => 14.99,
            'discount' => 0
        ],
        'jacket' => [
            'price' => 19.99,
            'discount' => 0
        ],
        'shoes' => [
            'price' => 24.99,
            'discount' => 0.1
        ]
    ];

    /**
     * @param array $potential_products
     * @return Collection
     */
    public function collectValidProducts(array $potential_products) : Collection
    {
        $this->products = collect($potential_products)
            ->reject(function ($product_name) {
                return !in_array(strtolower($product_name), array_keys(self::VALID_PRODUCTS));
            })
            ->map(function ($product_name) {
                return new Product(
                    strtolower($product_name), self::VALID_PRODUCTS[strtolower($product_name)]['price']
                );
            });

        return $this->products;
    }

    /**
     * @return Collection
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function isEmpty(): bool
    {
        return $this->products->isEmpty();
    }

    public function collectProductsAvailableForBundling($offer) : Collection
    {
        if(!isset($offer['bundled_with']) || !isset($offer['bundle_count'])) return  collect([]);

        return $this->products->filter(function ($product) use ($offer) {
            return $product->name == $offer['bundled_with'] && !$product->hasBeenUsed;
        })
            ->slice(0, $offer['bundle_count']);
    }

    public function markProductsAsUsedForBundling($products) : void
    {
        $products->each(function ($product) {
            $product->hasBeenUsed = true;
        });
    }
}
