<?php

namespace App\Actions\Auth;

use App\Data\Auth\Requests\RegisterUserData;
use App\Data\Auth\Results\AuthData;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
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
     * Register a new user within a transaction.
     *
     * @param RegisterUserData $userData
     * @return AuthData
     * @throws Throwable
     */
    public function handle(RegisterUserData $userData): AuthData
    {
        return DB::transaction(function () use ($userData) {
            $user = User::create($userData->toArray());
            $token = $this->issueTokenAction->handle($user);

            event(new Registered($user));

            return new AuthData(
                user: $user,
                token: $token,
            );
        });
    }
}
