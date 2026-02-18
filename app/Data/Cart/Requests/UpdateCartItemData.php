<?php

namespace App\Data\Cart\Requests;

use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class UpdateCartItemData extends Data
{
    /**
     * Create a new instance of UpdateCartItemData.
     *
     * @param int $quantity The new quantity for the cart item. Must be at least 1.
     */
    public function __construct(
        #[Required]
        #[IntegerType]
        #[Min(1)]
        public int $quantity,
    ) {}
}
