<?php

namespace App\Data\Auth;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class AuthData extends Data
{
    /**
     * Create a new instance of AuthData.
     *
     * @param UserData $user The authenticated user's data.
     * @param string $accessToken The access token for the authenticated user.
     * @param string $tokenType The type of the token, default is 'Bearer'.
     */
    public function __construct(
        public UserData $user,

        #[MapName('access_token')]
        public string $accessToken,

        #[MapName('token_type')]
        public string $tokenType = 'Bearer',
    ) {}
}
