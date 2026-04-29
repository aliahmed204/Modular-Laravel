<?php

namespace Modules\Order\Http\Controllers;

use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Modules\Order\Http\Requests\CheckoutRequest;
use Modules\Order\Models\Order;
use Modules\Payment\PayBuddy;
use Modules\Product\CartItem;
use Modules\Product\CartItemCollection;
use Modules\Product\Models\Product;
use Modules\Product\Warehouse\ProductStockManager;
use RuntimeException;

class CheckoutController
{
    public function __construct(
        public ProductStockManager $productStockManager,
    ) {}

    public function __invoke(CheckoutRequest $request)
    {
        $cartItems = CartItemCollection::fromRequest($request->input('products'));

        $orderTotalInCents = $cartItems->totalInCents();

        $payBuddy = PayBuddy::make();
        try {
            $charge = $payBuddy->charge(
                $request->input('payment_token'),
                $orderTotalInCents,
                'Payment for order #' . Order::latest()->first()?->id + 1 ?: 1
            );

            $order = Order::query()->create([
                'status' => 'completed',
                'total_in_cents' => $orderTotalInCents,
                'user_id' => $request->user()->id
            ]);

            $cartItems->items()->each(function ($cartItem) use ($order) {
                $order->lines()->create([
                    'product_id' => $cartItem->product->id,
                    'quantity' => $cartItem->quantity,
                    'product_price_in_cents' => $cartItem->product->priceInCents,
                ]);

                $this->productStockManager->reserveStock($cartItem->product->id, $cartItem->quantity);
            });

            $order->payments()->create([
                'total_in_cents' => $orderTotalInCents,
                'status' => 'paid',
                'payment_gateway' => 'PayBuddy',
                'payment_id' => $charge['id'],
                'user_id' => $request->user()->id,
            ]);

            return response()->json([
                'message' => 'Order created successfully.',
                'order_id' => $order->id,
            ], 201);

        } catch (RuntimeException) {
            throw ValidationException::withMessages([
                'payment_token' => 'We could not complete your payment.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An unexpected error occurred while processing your payment.',
            ], 302);
        }

    }
}