<?php

namespace Modules\Product\Listeners;

use Modules\Order\Events\OrderFullfilled;
use Modules\Product\Warehouse\ProductStockManager;

class DecreaseProductStock
{
    public function __construct(
        private ProductStockManager $productStockManager
    ) {}

    public function handle(OrderFullfilled $event)
    {
        $event->cartItems->items()->each(function ($cartItem) {
            $this->productStockManager->reserveStock($cartItem->product->id, $cartItem->quantity);
        });
    }
}
