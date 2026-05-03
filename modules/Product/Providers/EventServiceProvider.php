<?php

namespace Modules\Product\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as BaseEventServiceProvider;
use Modules\Order\Events\OrderFullfilled;
use Modules\Product\Listeners\DecreaseProductStock;

class EventServiceProvider extends BaseEventServiceProvider
{
    protected $listen = [
        OrderFullfilled::class => [
            DecreaseProductStock::class,
        ],
    ];
}
