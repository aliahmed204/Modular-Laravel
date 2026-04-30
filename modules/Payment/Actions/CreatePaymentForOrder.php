<?php

namespace Modules\Payment\Actions;

use Modules\Order\Exceptions\PaymentFailedException;
use Modules\Payment\PayBuddy;
use Modules\Payment\Payment;
use RuntimeException;

class CreatePaymentForOrder
{
    public function handle(
        int $orderId,
        int $userId,
        int $amountInCents,
        string $paymentToken,
        PayBuddy $payBuddy
    ) {
        // Charge the payment
        try {
            $charge = $payBuddy->charge(
                $paymentToken,
                $amountInCents,
                'Modularization'
            );
        } catch (RuntimeException) {
            throw PaymentFailedException::dueToInvalidToken();
        }

        // Payment
        Payment::query()->create([
            'total_in_cents' => $amountInCents,
            'status' => 'paid',
            'payment_gateway' => 'PayBuddy',
            'payment_id' => $charge['id'],
            'user_id' => $userId,
            'order_id' => $orderId,
        ]);
    }
}
