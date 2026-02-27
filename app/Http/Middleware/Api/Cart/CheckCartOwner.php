<?php

namespace App\Http\Middleware\Api\Cart;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckCartOwner
{
    /**
     * Handle an incoming request to ensure the cart owner is identified.
     *
     * @param Request $request
     * @param Closure(Request): (Response) $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        $guestToken = $request->header(config('cart.guest_token_header'));

        if (!$user && !$guestToken) {
            return response()->json(['message' => __('cart.errors.identification_missing')], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
