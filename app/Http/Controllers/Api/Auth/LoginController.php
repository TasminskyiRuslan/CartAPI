<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\LoginUserAction;
use App\Data\Auth\Requests\LoginUserRequestData;
use App\Data\Auth\Responses\AuthResponseData;
use App\Data\Auth\Responses\UserResponseData;
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
     * @param LoginUserRequestData $data The data for logging in a user.
     * @param LoginUserAction $action The action to handle user login.
     * @return JsonResponse A JSON response containing the authenticated user's data and access token.
     */
    public function __invoke(LoginUserRequestData $data, LoginUserAction $action): JsonResponse
    {
        [$user, $token] = $action->handle($data);

        $authData = new AuthResponseData(
            user: UserResponseData::from($user),
            accessToken: $token
        );

        return $authData
            ->toResponse(request())
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }
}
