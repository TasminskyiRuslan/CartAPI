<?php

namespace App\Actions\Auth;

use App\Models\User;

class IssueTokenAction
{
    /**
     * Generate a new personal access token for the user.
     *
     * @param User $user
     * @return string
     */
    public function handle(User $user): string
    {
        return $user->createToken('auth_token')->plainTextToken;
    }
}
