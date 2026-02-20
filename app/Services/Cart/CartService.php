<?php

namespace App\Services\Cart;

use App\Data\Cart\CartIdentifierData;
use App\Models\Cart;
use DB;
use Throwable;

class CartService
{
    /**
     * Retrieves an existing cart for the given identifier data or creates a new one if it doesn't exist.
     *
     * @param CartIdentifierData $data The data used to identify the cart, which may include user information and guest token.
     * @return Cart|null The retrieved or newly created cart instance.
     */
    public function get(CartIdentifierData $data): ?Cart
    {
        return Cart::forOwner($data)->active()->with('items.product')->first();
    }

    /**
     * Retrieves an existing cart for the given identifier data or creates a new one if it doesn't exist. If the cart is expired, it will be cleared and extended.
     *
     * @param CartIdentifierData $data The data used to identify the cart, which may include user information and guest token.
     * @throws Throwable If there is an error during the database transaction.
     * @return Cart The retrieved or newly created cart instance, with items and products loaded.
     */
    public function getOrCreate(CartIdentifierData $data): Cart
    {
        return DB::transaction(function () use ($data) {
            $cart = Cart::forOwner($data)->first();

            if (!$cart) {
                $cart = Cart::create([
                    'user_id' => $data->user?->id,
                    'guest_token' => $data->user ? null : $data->guestToken,
                ]);
            } elseif ($cart->isExpired()) {
                $cart->clear();
            }

            $cart->extendExpiration();

            return $cart->loadMissing('items.product');
        });
    }
}
