<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\LogoutUserAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class LogoutController extends Controller
{
    #[OA\Delete(
        path: '/auth/logout',
        description: 'Revoke the authentication token for the authenticated user.',
        summary: 'Logout current device',
        security: [['sanctum' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_NO_CONTENT,
                description: 'User logged out.'
            ),
            new OA\Response(
                response: SymfonyResponse::HTTP_UNAUTHORIZED,
                description: 'Unauthorized.'
            )
        ]
    )]
    /**
     * Handle the user logout request.
     *
     * @param Request $request
     * @param LogoutUserAction $logoutUserAction
     * @return Response
     */
    public function __invoke(Request $request, LogoutUserAction $logoutUserAction): Response
    {
        $logoutUserAction->handle($request->user());
        return response()->noContent();
    }
}
