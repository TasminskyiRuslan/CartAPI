<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\LoginUserAction;
use App\Data\Auth\Requests\LoginUserData;
use App\Data\Auth\Responses\AuthData;
use App\Data\Auth\Responses\UserData;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use OpenApi\Attributes as OA;

class LoginController extends Controller
{
    #[OA\Post(
        path: '/auth/login',
        description: 'Authenticate user.',
        summary: 'Login',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/LoginUserRequest')
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_OK,
                description: 'User logged in',
                content: new OA\JsonContent(ref: '#/components/schemas/Auth')
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY,
                description: 'Validation error'
            ),
        ]
    )]
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
