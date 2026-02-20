<?php

namespace App\Http\Resources\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @property-read int|null $id Unique identifier for the cart.
 * @property-read Collection|CartItemResource[] $items Collection of items in the cart, loaded when requested.
 * @method int calculateTotalItems() Calculate the total number of items in the cart by summing the quantities of all cart items, providing a clear view of the total item count in the cart.
 * @method string calculateTotalPrice() Calculate the total price of the cart by summing the total price of each cart item, providing a clear view of the overall cost of the items in the cart.
 */
class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if (is_null($this->resource)) {
            return [
                'id' => null,
                'items' => [],
                'total_items' => 0,
                'total_price' => '0.00',
            ];
        }

        return [
            'id' => $this->id,
            'items' => CartItemResource::collection($this->items),
            'total_items' => $this->calculateTotalItems(),
            'total_price' => $this->calculateTotalPrice(),
        ];
    }
}
