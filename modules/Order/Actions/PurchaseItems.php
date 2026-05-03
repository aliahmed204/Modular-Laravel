<?php

namespace Modules\Order\Actions;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\DatabaseManager;
use Modules\Order\Events\OrderFullfilled;
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
        private Dispatcher $eventDispatcher,
    ) {}

    public function execute(CartItemCollection $cartItems, PayBuddy $paymentProvider, string $paymentToken, int $userId, string $userEmail): Order
    {
        $order = $this->databaseManager->transaction(function () use ($cartItems, $paymentProvider, $paymentToken, $userId) {
            // Order
            $order = Order::startOrderCreation($userId)
                ->addLinesFromCollection($cartItems)
                ->fullfillOrder();

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

        $this->eventDispatcher->dispatch(new OrderFullfilled(
            $order->id,
            $order->localizedTotal(),
            $userId,
            $userEmail,
            $cartItems
        ));

        return $order;
    }
}
