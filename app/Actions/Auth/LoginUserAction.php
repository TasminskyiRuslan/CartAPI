<?php

namespace App\Actions\Auth;

use App\Data\Auth\Requests\LoginUserData;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginUserAction
{
    /**
     * Create a new instance of LoginUserAction.
     *
     * @param IssueTokenAction $issueTokenAction The action to handle token issuance.
     */
    public function __construct(
        protected IssueTokenAction $issueTokenAction,
    )
    {
    }

    /**
     * Handle the user login process.
     *
     * @param LoginUserData $data The data for logging in a user.
     * @return array{0: User, 1: string} An array containing the authenticated user and the issued token.
     * @throws ValidationException If the provided credentials are invalid.
     */
    public function handle(LoginUserData $data): array
    {
        $user = User::where('email', $data->email)->first();

        if (!$user || !Hash::check($data->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['auth.failed'],
            ]);
        }

        $token = $this->issueTokenAction->handle($user);

        return [$user, $token];
    }
}
