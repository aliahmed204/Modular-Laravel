<?php

namespace Modules\Order\Models;

use Illuminate\Database\Eloquent\Model;

class OrderLine extends Model
{
    protected $casts = [
        'order_id' => 'integer',
        'product_id' => 'integer',
        'product_price_in_cents' => 'integer',
        'quantity' => 'integer',
    ];
}
