<?php

namespace App\Actions\Auth;

use App\Models\User;

class IssueTokenAction
{
    /**
     * Issue an authentication token for the given user.
     *
     * @param User $user The user for whom the authentication token will be issued.
     * @return string The plain text authentication token for the user.
     */
    public function handle(User $user): string
    {
        return $user->createToken('auth_token')->plainTextToken;
    }
}
