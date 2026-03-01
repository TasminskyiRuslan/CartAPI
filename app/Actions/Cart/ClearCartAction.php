<?php

namespace App\Actions\Cart;

use App\Data\Cart\Context\CartIdentifierData;
use App\Models\Cart;

class ClearCartAction
{
    /**
     * Remove all items from the active cart for the given identifier.
     *
     * @param CartIdentifierData $identifierData
     * @return void
     */
    public function handle(CartIdentifierData $identifierData): void
    {
        Cart::forOwner($identifierData)->active()->first()?->items()->delete();
    }
}
