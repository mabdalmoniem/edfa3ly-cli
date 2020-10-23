<?php

namespace App\Models;

use Illuminate\Support\Collection;

class Product
{
    /**
     * @var string
     */
    public string $name;

    /**
     * @var float
     */
    public float $price;

    /**
     * @var float
     */
    public float $discount;

    /**
     * @var bool
     */
    public bool $hasBeenUsed;

    /**
     * @var bool
     */
    public bool $willBeDiscounted;

    /**
     * Product constructor.
     * @param string $name
     * @param float $price
     */
    public function __construct(string $name, float $price)
    {
        $this->name = $name;
        $this->price = $price;
        $this->hasBeenUsed = false;
        $this->willBeDiscounted = false;
    }

    public function applyDiscount($value, $setWillBeDiscountedToTrue = false)
    {
        if($setWillBeDiscountedToTrue) {
            $this->willBeDiscounted = true;
        }

        $this->price *= (1 - $value);
    }
}
