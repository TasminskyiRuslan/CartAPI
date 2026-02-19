<?php

namespace App\Services\Cart;

use App\Data\Cart\CartIdentifierData;
use App\Models\Cart;

class CartService
{
    public function getOrCreate(CartIdentifierData $data): Cart
    {
        if ($data->user) {
            return Cart::with('items.product')->firstOrCreate(
                ['user_id' => $data->user->id],
                ['guest_token' => null]
            );
        }

        return Cart::with('items.product')->firstOrCreate([
            'user_id' => null,
            'guest_token' => $data->guestToken,
        ]);
    }

    public function getCart(CartIdentifierData $data): Cart
    {
        $query = Cart::with('items.product');

        if ($data->user) {
            return $query->where('user_id', $data->user->id)->first() ?? Cart::make();
        }

        if ($data->guestToken) {
            return $query->where('guest_token', $data->guestToken)->first() ?? Cart::make();
        }

        return Cart::make();
    }


}
