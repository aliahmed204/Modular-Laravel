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
        return $this->databaseManager->transaction(function () use ($cartItems, $paymentProvider, $paymentToken, $userId) {
            // Order
            $order = Order::startOrderCreation($userId)
                ->addLinesFromCollection($cartItems)
                ->fullfillOrder();

            // Stock Reservation
            $cartItems->items()->each(function ($cartItem) use ($order) {
                $this->productStockManager->reserveStock($cartItem->product->id, $cartItem->quantity);
            });

            // Payment
            $this->createPaymentForOrder->handle(
                $order->id,
                $userId,
                $order->total_in_cents,
                $paymentToken,
                $paymentProvider
            );

            return $order;
        });
    }
}
