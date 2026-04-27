<?php

use App\Providers\AppServiceProvider;
use Modules\Order\Providers\OrderServiceProvider;
use Modules\Product\Providers\ProductServiceProvider;
use Modules\Shipment\Providers\ShipmentServiceProvider;

return [
    AppServiceProvider::class,
    OrderServiceProvider::class,
    ProductServiceProvider::class,
    ShipmentServiceProvider::class,
];
