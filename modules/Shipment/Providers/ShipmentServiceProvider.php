<?php

namespace Modules\Shipment\Providers;

use Illuminate\Support\ServiceProvider;

class ShipmentServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(module_path('Shipment', 'Database/Migrations'));
        $this->mergeConfigFrom(module_path('Shipment', 'config.php'), 'shipment');

        $this->app->register(RouteServiceProvider::class);
    }
}
