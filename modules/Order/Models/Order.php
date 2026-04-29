<?php

namespace Modules\Order\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $casts = [
        'user_id' => 'integer',
        'total_in_cents' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model', User::class));
    }

    public function lines()
    {
        return $this->hasMany(OrderLine::class);
    }
}
