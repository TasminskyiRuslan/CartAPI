<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\LoginUserAction;
use App\Data\Auth\Requests\LoginUserData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Auth\AuthResource;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class LoginController extends Controller
{
    #[OA\Post(
        path: '/auth/login',
        description: 'Authenticate user.',
        summary: 'Login',
        security: [['guest_token' => []], []],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/LoginUserRequest')
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_OK,
                description: 'User logged in.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/Auth'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY,
                description: 'Validation error.'
            ),
        ]
    )]
    /**
     * Handle the user login request.
     *
     * @param LoginUserData $data
     * @param LoginUserAction $action
     * @return JsonResponse
     */
    public function __invoke(LoginUserData $data, LoginUserAction $action): JsonResponse
    {
        $authData = $action->handle($data);

        return AuthResource::make($authData)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }
}
