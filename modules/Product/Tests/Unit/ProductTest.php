<?php

namespace Modules\Product\Tests\Unit;

// use Database\Factories\ProductFactory; // first approach: factory out of module
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Modules\Product\Database\Factories\ProductFactory as FactoriesProductFactory;
use Modules\Product\Models\Product;
use Modules\Product\Tests\ProductTestCase;

class ProductTest extends ProductTestCase
{
    use DatabaseMigrations;

    public function test_that_true_is_true(): void
    {
        $product = FactoriesProductFactory::new()->create();
        // $product = Product::factory()->create();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
        ]);

        $this->assertTrue(true);
    }
}
