<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\RevokeCurrentTokenAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class LogoutController extends Controller
{
    #[OA\Delete(
        path: '/auth/logout',
        description: 'Revoke the current access token for the authenticated user.',
        summary: 'Logout user',
        security: [['sanctum' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_NO_CONTENT,
                description: 'User logged out successfully.'
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNAUTHORIZED,
                description: 'Unauthenticated.'
            )
        ]
    )]
    /**
     * Logout the authenticated user by revoking the current access token.
     *
     * @param Request $request
     * @param RevokeCurrentTokenAction $revokeCurrentTokenAction
     * @return Response
     */
    public function __invoke(Request $request, RevokeCurrentTokenAction $revokeCurrentTokenAction): Response
    {
        $revokeCurrentTokenAction->handle($request->user());
        return response()->noContent();
    }
}
