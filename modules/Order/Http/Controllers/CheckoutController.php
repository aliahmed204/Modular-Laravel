<?php

namespace Modules\Order\Http\Controllers;

use Illuminate\Validation\ValidationException;
use Modules\Order\Http\Requests\CheckoutRequest;
use Modules\Order\Models\Order;
use Modules\Payment\PayBuddy;
use Modules\Product\Models\Product;
use RuntimeException;

class CheckoutController
{
    public function __invoke(CheckoutRequest $request)
    {
        $products = collect($request->input('products'))->map(function ($product) {
            return [
                'product' => Product::find($product['id']),
                'quantity' => $product['quantity'],
            ];
        });

        $orderTotalInCents = $products->reduce(function ($carry, $product) {
            return $carry + ($product['product']->price_in_cents * $product['quantity']);
        }, 0);

        $payBuddy = PayBuddy::make();
        try {
            $charge = $payBuddy->charge(
                $request->input('payment_token'),
                $orderTotalInCents,
                'Payment for order #' . Order::latest()->first()?->id + 1 ?: 1
            );

            $order = Order::query()->create([
                'payment_id' => $charge['id'],
                'status' => 'paid',
                'payment_gateway' => 'PayBuddy',
                'total_in_cents' => $orderTotalInCents,
                'user_id' => $request->user()->id
            ]);

            $products->each(function ($orderLine) use ($order) {
                $order->lines()->create([
                    'product_id' => $orderLine['product']->id,
                    'quantity' => $orderLine['quantity'],
                    'product_price_in_cents' => $orderLine['product']->price_in_cents,
                ]);

                $orderLine['product']->decrement('stock', $orderLine['quantity']);
            });

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