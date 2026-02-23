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
        description: 'Get the authenticated user\'s data.',
        summary: 'Get authenticated user',
        security: [['sanctum' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_OK,
                description: 'Authenticated user data.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/User'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNAUTHORIZED,
                description: 'Unauthorized.'
            )
        ]
    )]
    /**
     * Handle the incoming request to get the authenticated user's information.
     *
     * @param Request $request The incoming HTTP request.
     * @return JsonResponse A JSON response containing the authenticated user's data.
     */
    public function __invoke(Request $request): JsonResponse
    {
        return (new UserResource($request->user()))
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }
}
