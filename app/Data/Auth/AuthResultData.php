<?php

namespace App\Data\Auth;

use App\Models\User;
use Spatie\LaravelData\Data;

class AuthResultData extends Data
{
    /**
     * @param User $user
     * @param string $token
     * @param string|null $tokenType
     */
    public function __construct(
        public User $user,
        public string $token,
        public ?string $tokenType = 'Bearer',
    ) {}
}
