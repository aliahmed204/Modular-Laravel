<?php

namespace Modules\Order\Listeners;

use Illuminate\Support\Facades\Mail;
use Modules\Order\Events\OrderFullfilled;
use Modules\Order\Mail\OrderReceived;

class SendOrderConfirmationEmail
{
    public function handle(OrderFullfilled $event)
    {
        Mail::to($event->userEmail)->send(new OrderReceived($event->localizedOrderTotal));
    }
}
