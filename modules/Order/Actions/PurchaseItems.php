<?php

namespace Modules\Order\Actions;

use Illuminate\Database\DatabaseManager;
use Modules\Order\Models\Order;
use Modules\Payment\Actions\CreatePaymentForOrder;
use Modules\Payment\PayBuddy;
use Modules\Product\CartItemCollection;
use Modules\Product\Warehouse\ProductStockManager;

final class PurchaseItems
{
    public function __construct(
        private ProductStockManager $productStockManager,
        private CreatePaymentForOrder $createPaymentForOrder,
        private DatabaseManager $databaseManager,
    ) {}
    
    public function execute(CartItemCollection $cartItems, PayBuddy $paymentProvider, string $paymentToken, int $userId): Order
    {
        $orderTotalInCents = $cartItems->totalInCents();

        return $this->databaseManager->transaction(function () use ($cartItems, $paymentProvider, $paymentToken, $userId, $orderTotalInCents) {
            // Order
            $order = Order::query()->create([
                'status' => 'completed',
                'total_in_cents' => $orderTotalInCents,
                'user_id' => $userId
            ]);

            // Order Lines & Stock Reservation
            $cartItems->items()->each(function ($cartItem) use ($order) {
                $order->lines()->create([
                    'product_id' => $cartItem->product->id,
                    'quantity' => $cartItem->quantity,
                    'product_price_in_cents' => $cartItem->product->priceInCents,
                ]);

                $this->productStockManager->reserveStock($cartItem->product->id, $cartItem->quantity);
            });

            $this->createPaymentForOrder->handle(
                $order->id,
                $userId,
                $orderTotalInCents,
                $paymentToken,
                $paymentProvider
            );

            return $order;
        });
    }
}