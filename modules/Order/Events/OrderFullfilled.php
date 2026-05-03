<?php

namespace Modules\Order\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Product\CartItemCollection;

class OrderFullfilled
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $orderId,
        public string $localizedOrderTotal,
        public int $userId,
        public string $userEmail,
        public CartItemCollection $cartItems
    ) {}
}
