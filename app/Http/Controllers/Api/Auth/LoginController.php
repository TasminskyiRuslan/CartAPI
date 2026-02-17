<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\LoginUserAction;
use App\Data\Auth\Requests\LoginUserData;
use App\Data\Auth\Responses\AuthData;
use App\Data\Auth\Responses\UserData;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class LoginController extends Controller
{
    /**
     * Handle the incoming login request.
     *
     * @param LoginUserData $data The data for logging in a user.
     * @param LoginUserAction $action The action to handle user login.
     * @return JsonResponse A JSON response containing the authenticated user's data and access token.
     */
    public function __invoke(LoginUserData $data, LoginUserAction $action): JsonResponse
    {
        [$user, $token] = $action->handle($data);

        $authData = new AuthData(
            user: UserData::from($user),
            accessToken: $token
        );

        return $authData
            ->toResponse(request())
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }
}
