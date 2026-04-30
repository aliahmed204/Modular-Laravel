<?php

namespace Modules\Order\Http\Controllers;

use Illuminate\Validation\ValidationException;
use Modules\Order\Actions\PurchaseItems;
use Modules\Order\Exceptions\PaymentFailedException;
use Modules\Order\Http\Requests\CheckoutRequest;
use Modules\Payment\PayBuddy;
use Modules\Product\CartItemCollection;

class CheckoutController
{
    public function __construct(
        public PurchaseItems $purchaseItems,
    ) {}

    public function __invoke(CheckoutRequest $request)
    {
        $cartItems = CartItemCollection::fromRequest($request->input('products'));

        try {
            $order = $this->purchaseItems->execute(
                $cartItems,
                PayBuddy::make(),
                $request->input('payment_token'),
                $request->user()->id
            );

            return response()->json([
                'message' => 'Order created successfully.',
                'order_url' => $order->url(),
            ], 201);

        } catch (PaymentFailedException $e) {
            throw ValidationException::withMessages([
                'payment_token' => $e->getMessage(),
            ]);
        } catch (\Exception $e) {
            dd($e->getMessage());
            return response()->json([
                'message' => 'An unexpected error occurred while processing your order.',
            ], 302);
        }

    }
}