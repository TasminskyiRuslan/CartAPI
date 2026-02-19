<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read int $id Unique identifier for the product.
 * @property-read string $name Name of the product.
 * @property-read string|null $description Optional description of the product, providing additional details for customers.
 * @property-read string $price Current price of the product, providing a clear view of its cost to the customer.
 * @property-read string|null $image_path Optional path to the product's image, allowing for visual representation of the product in the application.
 */
class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'image_path' => $this->image_path,
        ];
    }
}
