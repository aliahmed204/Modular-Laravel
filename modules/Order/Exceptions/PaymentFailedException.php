<?php

namespace Modules\Order\Exceptions;

use RuntimeException;

class PaymentFailedException extends RuntimeException
{
    public static function dueToInvalidToken(): self
    {
        return new self('Payment failed due to invalid token.');
    }
}
