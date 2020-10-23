<?php

namespace Tests\Unit\Models;

use App\Models\Product;
use Tests\TestCase;

class ProductTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_apply_discount()
    {
        // Arrange
        $product = new Product("Hat", 10);

        // Act
        $this->assertEquals(10, $product->price);
        $product->applyDiscount(0.5);

        // Assert
        $this->assertEquals(5, $product->price);
    }

    /**
     * @test
     */
    public function it_marks_product_as_will_be_discounted()
    {
        // Arrange
        $product = new Product("Hat", 10);

        // Act
        $this->assertEquals(10, $product->price);
        $this->assertFalse($product->willBeDiscounted);
        $product->applyDiscount(0.5, true);

        // Assert
        $this->assertEquals(5, $product->price);
        $this->assertTrue($product->willBeDiscounted);
    }
}
