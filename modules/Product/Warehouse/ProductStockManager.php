<?php

namespace Modules\Product\Warehouse;

use Modules\Product\Models\Product;

class ProductStockManager
{
    public function reserveStock(int $productId, int $amount): void
    {
        Product::query()->find($productId)?->decrement('stock', $amount);
    }

    public function releaseStock(int $productId, int $amount): void
    {
        Product::query()->find($productId)?->increment('stock', $amount);
    }
}
