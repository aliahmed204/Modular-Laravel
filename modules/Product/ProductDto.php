<?php

namespace Modules\Product;

use Modules\Product\Models\Product;

readonly class ProductDto
{
    public function __construct(
        public int $id,
        public int $priceInCents,
        public int $unitsInStock,
    ) {}

    public static function fromModel(Product $product): self
    {
        return new ProductDto(
            id: $product->id,
            priceInCents: $product->price_in_cents,
            unitsInStock: $product->stock,
        );
    }
}
