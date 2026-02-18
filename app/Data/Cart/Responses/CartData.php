<?php

namespace App\Data\Cart\Responses;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;

class CartData extends Data
{
    /**
     * Create a new instance of CartData.
     *
     * @param int $id The unique identifier of the cart.
     * @param Collection $items A collection of CartItemData representing the items in the cart.
     * @param int $totalItems The total number of items in the cart.
     * @param string $totalPrice The total price of all items in the cart, formatted as a string.
     */
    public function __construct(
        public int                  $id,

        #[DataCollectionOf(CartItemData::class)]
        public Collection $items,

        #[MapOutputName('total_items')]
        public int                  $totalItems,

        #[MapOutputName('total_price')]
        public string               $totalPrice,
    ) {}
}
