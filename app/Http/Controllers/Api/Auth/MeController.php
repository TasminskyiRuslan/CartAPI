<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Auth\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class MeController extends Controller
{
    #[OA\Get(
        path: '/auth/me',
        description: 'Return the currently authenticated user.',
        summary: 'Get current user',
        security: [['sanctum' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_OK,
                description: 'User retrieved successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/UserResponse'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNAUTHORIZED,
                description: 'Unauthenticated.'
            )
        ]
    )]
    /**
     * Retrieve the currently authenticated user.
     *
     * @return JsonResponse
     */
    public function __invoke(): JsonResponse
    {
        return UserResource::make(auth()->user())
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }
}
