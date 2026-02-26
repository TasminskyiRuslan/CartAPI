<?php

namespace App\Data\Cart\Requests;

use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class UpdateCartItemData extends Data
{
    /**
     * Create a new data transfer object instance.
     *
     * @param int $quantity
     */
    public function __construct(
        #[Required]
        #[IntegerType]
        #[Min(1)]
        public int $quantity,
    ) {}

    /**
     * Get the validation rules for the request.
     *
     * @return array
     */
    public static function rules(): array
    {
        return [
            'quantity' => [
                'required',
                'integer',
                'min:1',
                'max:' . config('cart.max_quantity'),
            ],
        ];
    }
}
