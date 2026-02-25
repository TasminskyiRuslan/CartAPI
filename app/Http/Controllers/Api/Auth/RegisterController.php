<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\RegisterUserAction;
use App\Data\Auth\Requests\RegisterUserData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Auth\AuthResource;
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
        security: [['guest_token' => []], []],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/RegisterUserRequest')
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_CREATED,
                description: 'User registered.',
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
     * Handle the user registration request.
     *
     * @param RegisterUserData $data
     * @param RegisterUserAction $action
     * @return JsonResponse
     * @throws Throwable
     */
    public function __invoke(RegisterUserData $data, RegisterUserAction $action): JsonResponse
    {
        $authData = $action->handle($data);

        return AuthResource::make($authData)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_CREATED);
    }
}
