<?php

namespace Modules\Product\Models;

// first approach: factory out of module
// use Database\Factories\ProductFactory;

use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Product\Database\Factories\ProductFactory;

#[UseFactory(ProductFactory::class)]
class Product extends Model
{
    use HasFactory;
}
