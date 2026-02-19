<?php

namespace App\Http\Resources\Cart;

use App\Http\Resources\Product\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read int $id Unique identifier for the cart item.
 * @property-read array $price_snapshot Snapshot of the product's price at the time it was added to the cart, including details such as base price, discounts, and taxes.
 * @property-read int $quantity The quantity of the product added to the cart.
 * @method string calculateTotalPrice() Calculate the total price for this cart item by multiplying the quantity with the price from the price snapshot, providing a clear view of the cost for this specific item in the cart.
 * @property-read Product|null $product The product associated with the cart item, providing access to its details such as name, description, and current price.
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
