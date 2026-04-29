<?php

namespace Modules\Payment;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Modules\Order\Models\Order;

class Payment extends Model
{
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
