<?php

namespace App\Data\Auth;

use App\Models\User;
use Spatie\LaravelData\Data;

class AuthData extends Data
{
    /**
     * Create a new instance of AuthData.
     *
     * @param User $user The authenticated user.
     * @param string $token The token issued for the authenticated user.
     * @param string|null $tokenType The type of the token (e.g., 'Bearer').
     */
    public function __construct(
        public User $user,
        public string $token,
        public ?string $tokenType = 'Bearer',
    ) {}
}
