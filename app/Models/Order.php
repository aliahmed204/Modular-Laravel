<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $casts = [
        'user_id' => 'integer',
        'total_in_cents' => 'integer',
    ];
}
