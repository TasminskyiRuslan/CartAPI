<?php

namespace App\Data\Auth\Responses;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class AuthResponseData extends Data
{
    /**
     * Create a new instance of AuthResponseData.
     *
     * @param UserResponseData $user The authenticated user's data.
     * @param string $accessToken The access token for the authenticated user.
     * @param string $tokenType The type of the token, default is 'Bearer'.
     */
    public function __construct(
        public UserResponseData $user,

        #[MapName('access_token')]
        public string           $accessToken,

        #[MapName('token_type')]
        public string           $tokenType = 'Bearer',
    ) {}
}
