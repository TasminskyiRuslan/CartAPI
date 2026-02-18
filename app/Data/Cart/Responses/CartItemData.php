<?php

namespace App\Data\Cart\Responses;

use App\Data\Product\Responses\ProductData;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;

class CartItemData extends Data
{
    /**
     * Create a new instance of CartItemData.
     *
     * @param int $id The unique identifier of the cart item.
     * @param string $priceSnapshot The price of the product at the time it was added to the cart, formatted as a string.
     * @param int $quantity The quantity of the product in the cart.
     * @param string $totalPrice The total price for this cart item (priceSnapshot multiplied by quantity), formatted as a string.
     * @param ProductData $product The product data associated with this cart item.
     */
    public function __construct(
        public int         $id,

        #[MapOutputName('price_snapshot')]
        public string      $priceSnapshot,

        public int         $quantity,

        #[MapOutputName('total_price')]
        public string      $totalPrice,

        public ProductData $product,
    ) {}
}
