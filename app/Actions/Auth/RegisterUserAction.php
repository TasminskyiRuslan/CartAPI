<?php

namespace App\Actions\Auth;

use App\Data\Auth\RegisterUserData;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Throwable;

class RegisterUserAction
{
    /**
     * Register a new user and issue an authentication token.
     *
     * @param IssueTokenAction $issueTokenAction The action to handle token issuance.
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
     * @return array{0: User, 1: string} An array containing the registered user and the issued token.
     * @throws Throwable If an error occurs during the registration process.
     */
    public function handle(RegisterUserData $data): array
    {
        return DB::transaction(function () use ($data) {
            $user = User::create($data->toArray());
            $token = $this->issueTokenAction->handle($user);

            return [$user, $token];
        });
    }
}
