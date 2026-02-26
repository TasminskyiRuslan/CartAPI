<?php

namespace App\Data\Cart\Context;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\LaravelData\Data;

class CartIdentifierData extends Data
{
    /**
     * @param User|null $user
     * @param string|null $guestToken
     */
    public function __construct(
        public ?User $user,
        public ?string $guestToken,
    ) {}

    /**
     * Create an instance from the current request.
     *
     * @param Request $request
     * @return self
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            user: $request->user(),
            guestToken: $request->header(config('cart.guest_header'))
        );
    }
}
