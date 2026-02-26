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
     * @param RegisterUserData $data
     * @return AuthData
     * @throws Throwable
     */
    public function handle(RegisterUserData $data): AuthData
    {
        return DB::transaction(function () use ($data) {
            $user = User::create($data->toArray());
            $token = $this->issueTokenAction->handle($user);

            event(new Registered($user));

            return new AuthData(
                user: $user,
                token: $token,
            );
        });
    }
}
