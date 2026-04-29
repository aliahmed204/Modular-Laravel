<?php

namespace Modules\Order\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Product\Models\Product;

class OrderLine extends Model
{
    protected $casts = [
        'order_id' => 'integer',
        'product_id' => 'integer',
        'product_price_in_cents' => 'integer',
        'quantity' => 'integer',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
