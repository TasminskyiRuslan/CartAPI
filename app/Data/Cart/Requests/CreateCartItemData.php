<?php

namespace App\Data\Cart\Requests;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class CreateCartItemData extends Data
{
    /**
     * @param int $productId
     * @param int $quantity
     */
    public function __construct(
        #[Required]
        #[IntegerType]
        #[Exists('products', 'id')]
        #[MapInputName('product_id')]
        public int $productId,

        #[IntegerType]
        #[Min(1)]
        public int $quantity = 1,
    ) {}
}
