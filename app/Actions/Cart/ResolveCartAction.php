<?php

namespace App\Actions\Cart;

use App\Data\Cart\Context\CartIdentifierData;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;
use Throwable;

class ResolveCartAction
{
    /**
     * Resolve the active cart for the given identifier, creating a new one if it doesn't exist or is expired.
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
