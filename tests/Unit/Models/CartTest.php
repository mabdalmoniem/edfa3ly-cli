<?php


namespace Tests\Unit\Models;

use App\Models\Bill;
use App\Models\Cart;
use App\Models\Product;
use Tests\TestCase;

class CartTest extends TestCase
{
    /**
     * @test
     */
    public function it_collects_valid_products()
    {
        // Arrange
        $potential_products = ['t-shirt', 'shoes', 'jacket'];
        $cart = new Cart;

        // Act
        $cart->collectValidProducts($potential_products);

        // Assert
        $this->assertEquals(3,$cart->getProducts()->count());
    }

    /**
     * @test
     */
    public function it_ignores_invalid_products()
    {
        // Arrange
        $potential_products = ['watch', 'laptop', 'mug'];
        $cart = new Cart;

        // Act
        $cart->collectValidProducts($potential_products);;

        // Assert
        $this->assertEquals(0, $cart->getProducts()->count());
    }

    /**
     * @test
     */
    public function it_collects_valid_products_while_being_case_insensitive()
    {
        // Arrange
        $potential_products = ['T-ShIrT', 'sHoes', 'jAckEt'];
        $cart = new Cart;

        // Act
        $cart->collectValidProducts($potential_products);

        // Assert
        $this->assertEquals(3, $cart->getProducts()->count());
    }

    /**
     * @test
     */
    public function it_contains_products()
    {
        // Arrange
        $potential_products = ['t-shirt', 'shoes', 'jacket'];
        $cart = new Cart;

        // Act
        $cart->collectValidProducts($potential_products);;

        // Assert
        $this->assertInstanceOf(Product::class, $cart->getProducts()->get(0));
        $this->assertInstanceOf(Product::class, $cart->getProducts()->get(1));
        $this->assertInstanceOf(Product::class, $cart->getProducts()->get(2));
    }

    /**
     * @test
     */
    public function it_collects_products_available_for_bundling()
    {
        // Arrange
        $potential_products = ['t-shirt', 'shoes', 'jacket', 't-shirt'];
        $jacket_offer = Bill::CURRENT_OFFERS['jacket'];
        $cart = new Cart;

        // Act
        $cart->collectValidProducts($potential_products);;
        $products = $cart->collectProductsAvailableForBundling($jacket_offer);

        // Assert
        $this->assertCount(2, $products);
        $this->assertEquals('t-shirt', $products->first()->name);
        $this->assertEquals('t-shirt', $products->last()->name);
        $this->assertFalse($products->first()->hasBeenUsed);
        $this->assertFalse($products->last()->hasBeenUsed);
    }

    /**
     * @test
     */
    public function it_marks_products_as_used()
    {
        // Arrange
        $potential_products = ['t-shirt', 'shoes', 'jacket', 't-shirt'];
        $jacket_offer = Bill::CURRENT_OFFERS['jacket'];
        $cart = new Cart;
        $cart->collectValidProducts($potential_products);;
        $products = $cart->collectProductsAvailableForBundling($jacket_offer);
        $products->each(function ($product) {
            $this->assertFalse($product->hasBeenUsed);
        });

        // Act
        $cart->markProductsAsUsedForBundling($products);

        // Assert
        $products->each(function ($product) {
            $this->assertTrue($product->hasBeenUsed);
        });
    }
}
