<?php

namespace App\Actions\Auth;

use App\Models\User;

class LogoutUserAction
{
    /**
     * Handle the logout process for the given user.
     *
     * @param User $user The user to be logged out.
     * @return void
     */
    public function handle(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }
}
