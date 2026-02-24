<?php

namespace App\Data\Cart;

use App\Models\Cart;
use Spatie\LaravelData\Data;

class AddCartItemResultData extends Data
{
    public function __construct(
        public Cart $cart,
        public bool $created
    ) {}
}
