<?php

namespace App\Data\Cart\Results;

use App\Models\Cart;
use Spatie\LaravelData\Data;

class AddCartItemData extends Data
{
    /**
     * Create a new data transfer object instance.
     *
     * @param Cart $cart
     * @param bool $created
     */
    public function __construct(
        public Cart $cart,
        public bool $created
    ) {}
}
