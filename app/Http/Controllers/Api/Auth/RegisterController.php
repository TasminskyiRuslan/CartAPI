<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\RegisterUserAction;
use App\Data\Auth\Requests\RegisterUserRequestData;
use App\Data\Auth\Responses\AuthResponseData;
use App\Data\Auth\Responses\UserResponseData;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class RegisterController extends Controller
{
    #[OA\Post(
        path: '/auth/register',
        description: 'Register a new user.',
        summary: 'Register',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/RegisterUserRequest')
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_CREATED,
                description: 'User registered',
                content: new OA\JsonContent(ref: '#/components/schemas/Auth')
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY,
                description: 'Validation error'
            ),
        ]
    )]
    /**
     * Handle the incoming registration request.
     *
     * @param RegisterUserRequestData $data The data for registering a new user.
     * @param RegisterUserAction $action The action to handle user registration.
     * @return JsonResponse A JSON response containing the authenticated user's data and access token.
     * @throws Throwable If an error occurs during the registration process.
     */
    public function __invoke(RegisterUserRequestData $data, RegisterUserAction $action): JsonResponse
    {
        [$user, $token] = $action->handle($data);

        $authData = new AuthResponseData(
            user: UserResponseData::from($user),
            accessToken: $token
        );

        return $authData
            ->toResponse(request())
            ->setStatusCode(SymfonyResponse::HTTP_CREATED);
    }
}
