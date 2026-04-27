<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $casts = [
        'quantity' => 'integer',
        'user_id' => 'integer',
        'product_id' => 'integer',
    ];
}
