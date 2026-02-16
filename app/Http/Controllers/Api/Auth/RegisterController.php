<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\IssueTokenAction;
use App\Actions\Auth\RegisterUserAction;
use App\Data\Auth\AuthData;
use App\Data\Auth\RegisterUserData;
use App\Data\Auth\UserData;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Throwable;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class RegisterController extends Controller
{
    /**
     * Handle the incoming registration request.
     *
     * @param RegisterUserData $data The data for registering a new user.
     * @param RegisterUserAction $registerUserAction The action to handle user registration.
     * @param IssueTokenAction $issueTokenAction The action to handle token issuance.
     * @return JsonResponse A JSON response containing the authenticated user's data and access token.
     * @throws Throwable If an error occurs during the registration process.
     */
    public function __invoke(RegisterUserData $data, RegisterUserAction $registerUserAction, IssueTokenAction $issueTokenAction): JsonResponse
    {
        [$user, $token] = $registerUserAction->handle($data);
        $authData = new AuthData(
            user: UserData::from($user),
            accessToken: $token
        );

        return $authData
            ->toResponse(request())
            ->setStatusCode(SymfonyResponse::HTTP_CREATED);
    }
}
