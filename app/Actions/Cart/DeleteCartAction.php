<?php

namespace App\Actions\Cart;

use App\Data\Cart\Context\CartIdentifierData;
use App\Models\Cart;

class DeleteCartAction
{
    /**
     * Delete the cart identified by the given identifier data.
     *
     * @param CartIdentifierData $identifierData
     * @return void
     */
    public function handle(CartIdentifierData $identifierData): void
    {
        Cart::forOwner($identifierData)->delete();
    }
}
