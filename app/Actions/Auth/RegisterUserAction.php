<?php

namespace App\Actions\Auth;

use App\Data\Auth\AuthData;
use App\Data\Auth\Requests\RegisterUserData;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Throwable;

class RegisterUserAction
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
     * Handle the user registration process.
     *
     * @param RegisterUserData $data The data for registering a new user.
     * @throws Throwable If an error occurs during the registration process.
     * @return AuthData An array containing the newly registered user and the issued token.
     */
    public function handle(RegisterUserData $data): AuthData
    {
        return DB::transaction(function () use ($data) {
            $user = User::create($data->toArray());
            $token = $this->issueTokenAction->handle($user);

            return new AuthData(
                user: $user,
                token: $token,
            );
        });
    }
}
