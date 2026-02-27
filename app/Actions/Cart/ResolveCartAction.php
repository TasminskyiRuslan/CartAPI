<?php

namespace App\Actions\Cart;

use App\Data\Cart\Context\CartIdentifierData;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;
use Throwable;

class ResolveCartAction
{
    /**
     * Retrieve the active cart for the given identifier.
     *
     * If no cart exists or the existing cart is expired, a new cart will be created.
     *
     * @param CartIdentifierData $identifierData
     * @return Cart
     * @throws Throwable
     */
    public function handle(CartIdentifierData $identifierData): Cart
    {
        return DB::transaction(function () use ($identifierData) {
            $cart = Cart::forOwner($identifierData)->lockForUpdate()->first();

            if (!$cart || $cart->isExpired()) {
                $cart?->delete();
                $cart = Cart::create([
                    'user_id' => $identifierData->user?->id,
                    'guest_token' => $identifierData->user?->id ? null : $identifierData->guestToken
                ]);
            }

            return $cart;
        });
    }
}
