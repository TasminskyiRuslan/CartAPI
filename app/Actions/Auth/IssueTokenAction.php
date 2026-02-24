<?php

namespace App\Actions\Auth;

use App\Models\User;

class IssueTokenAction
{
    /**
     * Issue an authentication token for the given user.
     *
     * @param User $user
     * @return string
     */
    public function handle(User $user): string
    {
        return $user->createToken('auth_token')->plainTextToken;
    }
}
