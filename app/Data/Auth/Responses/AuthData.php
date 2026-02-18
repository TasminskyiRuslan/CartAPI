<?php

namespace App\Data\Auth\Responses;

use Spatie\LaravelData\Attributes\MapOutputName;
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

        #[MapOutputName('access_token')]
        public string   $accessToken,

        #[MapOutputName('token_type')]
        public string   $tokenType = 'Bearer',
    ) {}
}
