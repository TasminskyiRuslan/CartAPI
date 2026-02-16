<?php

namespace App\Data\Auth;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class AuthData extends Data
{
    public function __construct(
        public UserData $user,
        public string $token,

        #[MapName('token_type')]
        public string $tokenType = 'Bearer',
    ) {}
}
