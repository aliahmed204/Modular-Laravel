<?php

namespace Modules\Product;

use Illuminate\Support\Collection;
use Modules\Product\Models\Product;

class CartItemCollection
{
    /**
     * @param  Collection<CartItem>  $items
     */
    public function __construct(
        protected Collection $items,
    ) {}

    public static function fromRequest(array $products): self
    {
        $items = collect($products)->map(function ($product) {
            return new CartItem(
                ProductDto::fromModel(Product::findOrFail($product['id'])),
                $product['quantity']
            );
        });

        return new self($items);
    }

    public function totalInCents(): int
    {
        return $this->items->sum(function ($cartItem) {
            return $cartItem->product->priceInCents * $cartItem->quantity;
        });
    }

    /** @param Collection<CartItem> */
    public function items(): Collection
    {
        return $this->items;
    }
}
