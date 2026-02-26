<?php

namespace App\Data\Cart\Results;

use App\Models\Cart;
use Spatie\LaravelData\Data;

class AddCartItemData extends Data
{
    public function __construct(
        public Cart $cart,
        public bool $created
    ) {}
}
