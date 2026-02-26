<?php

namespace App\Actions\Auth;

use App\Data\Auth\Requests\LoginUserData;
use App\Data\Auth\Results\AuthData;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginUserAction
{
    /**
     * @param IssueTokenAction $issueTokenAction
     */
    public function __construct(
        protected IssueTokenAction $issueTokenAction,
    )
    {
    }

    /**
     * Authenticate user and issue token.
     *
     * @param LoginUserData $data
     * @return AuthData
     * @throws ValidationException
     */
    public function handle(LoginUserData $data): AuthData
    {
        $user = User::where('email', $data->email)->first();

        if (!$user || !Hash::check($data->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['auth.failed'],
            ]);
        }

        $token = $this->issueTokenAction->handle($user);

        event(new Login(config('auth.defaults.guard'), $user, false));

        return new AuthData(
            user: $user,
            token: $token,
        );
    }
}
