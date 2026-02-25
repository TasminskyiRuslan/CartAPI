<?php

namespace App\Data\Cart\Requests;

use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class UpdateCartItemData extends Data
{
    /**
     * @param int $quantity
     */
    public function __construct(
        #[Required]
        #[IntegerType]
        #[Min(1)]
        #[Max(99)]
        public int $quantity,
    ) {}
}
