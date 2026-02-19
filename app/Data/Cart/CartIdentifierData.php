<?php

namespace App\Data\Cart;

use App\Models\User;
use Spatie\LaravelData\Data;

class CartIdentifierData extends Data
{
    /**
     * Create a new instance of CartIdentifierData.
     *
     * @param User|null $user The authenticated user associated with the cart, or null if the cart belongs to a guest.
     * @param string|null $guestToken The unique token for identifying a guest user's cart, or null if the cart belongs to an authenticated user.
     */
    public function __construct(
        public ?User $user,
        public ?string $guestToken,
    ) {}
}
