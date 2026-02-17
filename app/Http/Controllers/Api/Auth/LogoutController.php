<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\LogoutUserAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LogoutController extends Controller
{
    /**
     * Handle the incoming request to logout the authenticated user.
     *
     * @param Request $request The incoming HTTP request containing the authenticated user.
     * @param LogoutUserAction $action The action responsible for logging out the user.
     * @return Response A response indicating the result of the logout operation.
     */
    public function __invoke(Request $request, LogoutUserAction $action): Response
    {
        $action->handle($request->user());
        return response()->noContent();
    }
}
