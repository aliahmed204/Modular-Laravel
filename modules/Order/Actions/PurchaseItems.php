<?php

namespace Modules\Order\Actions;

use Modules\Order\Exceptions\PaymentFailedException;
use Modules\Order\Models\Order;
use Modules\Payment\PayBuddy;
use Modules\Product\CartItemCollection;
use Modules\Product\Warehouse\ProductStockManager;
use RuntimeException;

final class PurchaseItems
{
    public function __construct(
        private ProductStockManager $productStockManager
    ) {}
    
    public function execute(CartItemCollection $cartItems, PayBuddy $paymentProvider, string $paymentToken, int $userId): Order
    {
        $orderTotalInCents = $cartItems->totalInCents();

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

        // Charge the payment
        try {
            $charge = $paymentProvider->charge(
                $paymentToken,
                $orderTotalInCents,
                'Modularization'
            );
        } catch (RuntimeException) {
            throw PaymentFailedException::dueToInvalidToken();
        }

        // Payment
        $order->payments()->create([
            'total_in_cents' => $orderTotalInCents,
            'status' => 'paid',
            'payment_gateway' => 'PayBuddy',
            'payment_id' => $charge['id'],
            'user_id' => $userId,
        ]);

        return $order;
    }
}