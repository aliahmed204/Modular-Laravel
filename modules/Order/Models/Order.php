<?php

namespace Modules\Order\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Modules\Payment\Payment;

class Order extends Model
{
    protected $casts = [
        'user_id' => 'integer',
        'total_in_cents' => 'integer',
    ];

    // Methods
    public function url(): string
    {
        return route('order::orders.show', $this);
    }

    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model', User::class));
    }

    public function lines()
    {
        return $this->hasMany(OrderLine::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function lastPayment()
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }
}
