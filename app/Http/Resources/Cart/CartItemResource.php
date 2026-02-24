<?php

namespace App\Http\Resources\Cart;

use App\Http\Resources\Product\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read int $id
 * @property-read string $price_snapshot
 * @property-read int $quantity
 * @property-read Product|null $product
 * @method string calculateTotalPrice()
 */
class CartItemResource extends JsonResource
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
            'price_snapshot' => $this->price_snapshot,
            'quantity' => $this->quantity,
            'total_price' => $this->calculateTotalPrice(),
            'product' => new ProductResource($this->product),
        ];
    }
}
