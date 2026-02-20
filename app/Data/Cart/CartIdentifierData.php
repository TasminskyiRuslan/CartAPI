<?php

namespace App\Data\Cart;

use App\Models\User;
use Illuminate\Http\Request;
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

    /**
     * Create a new instance of CartIdentifierData from an HTTP request.
     *
     * @param Request $request The incoming HTTP request containing the authenticated user and guest token (if applicable).
     * @return self A new instance of CartIdentifierData populated with the user and guest token from the request.
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            user: $request->user(),
            guestToken: $request->header(config('cart.guest_header'))
        );
    }
}
