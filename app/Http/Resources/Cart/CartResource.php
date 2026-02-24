<?php

namespace App\Http\Resources\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @property-read int|null $id
 * @property-read Collection|CartItemResource[] $items
 * @method int calculateTotalItems()
 * @method string calculateTotalPrice()
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
