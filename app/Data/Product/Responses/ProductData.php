<?php

namespace App\Data\Product\Responses;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class ProductData extends Data
{
    /**
     * Create a new instance of ProductData.
     *
     * @param int $id The unique identifier of the product.
     * @param string $name The name of the product.
     * @param string|null $description A brief description of the product.
     * @param string $price
     * @param string|null $imagePath The URL path to the product's image.
     */
    public function __construct(
        public int $id,

        public string $name,

        public ?string $description,

        public string $price,

        #[MapName('image_path')]
        public ?string $imagePath,
    ) {}
}
