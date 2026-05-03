<?php

namespace Modules\Order\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Modules\Order\Exceptions\OrderMissingOrderLinesException;
use Modules\Payment\Payment;
use Modules\Product\CartItemCollection;
use NumberFormatter;

class Order extends Model
{
    protected $casts = [
        'user_id' => 'integer',
        'total_in_cents' => 'integer',
    ];

    public const COMPLETED = 'completed';

    public const PENDING = 'pending';

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

    // Methods
    public function url(): string
    {
        return route('order::orders.show', $this);
    }

    public function localizedTotal(): string
    {
        return (new NumberFormatter('en-US', NumberFormatter::CURRENCY))->formatCurrency($this->total_in_cents / 100, 'USD');
    }

    public static function startOrderCreation(int $userId): self
    {
        return self::make([
            'status' => self::PENDING,
            'user_id' => $userId,
        ]);
    }

    public function addLinesFromCollection(CartItemCollection $cartItems): self
    {
        $cartItems->items()->each(function ($cartItem) {
            $this->lines->push(OrderLine::make([
                'product_id' => $cartItem->product->id,
                'quantity' => $cartItem->quantity,
                'product_price_in_cents' => $cartItem->product->priceInCents,
            ]));
        });

        $this->total_in_cents = $cartItems->totalInCents();

        return $this;
    }

    public function fullfillOrder(): self
    {
        if ($this->lines->isEmpty()) {
            throw new OrderMissingOrderLinesException;
        }

        $this->status = self::COMPLETED;
        $this->save();
        $this->lines()->saveMany($this->lines);

        return $this;
    }
}
