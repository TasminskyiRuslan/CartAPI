<?php

namespace App\Actions\Cart;

use App\Data\Cart\Context\CartIdentifierData;
use App\Models\Cart;

class FindCartAction
{
    /**
     * Retrieve the active cart for the given identifier.
     *
     * @param CartIdentifierData $identifierData
     * @return Cart|null
     */
    public function handle(CartIdentifierData $identifierData): ?Cart
    {
        return Cart::forOwner($identifierData)->active()->first();
    }
}
