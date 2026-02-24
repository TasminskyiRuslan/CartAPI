<?php

namespace App\Actions\Cart;

use App\Data\Cart\CartIdentifierData;
use App\Models\Cart;

class FindCartAction
{
    /**
     * Find the active cart for the given identifier data.
     *
     * @param CartIdentifierData $identifierData
     * @return Cart|null
     */
    public function handle(CartIdentifierData $identifierData): ?Cart
    {
        return Cart::forOwner($identifierData)->active()->first();
    }
}
