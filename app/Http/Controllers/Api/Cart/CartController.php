<?php

namespace App\Http\Controllers\Api\Cart;

use App\Data\Cart\CartIdentifierData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Cart\CartResource;
use App\Services\Cart\CartService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class CartController extends Controller
{
    /**
     * CartController constructor.
     *
     * @param CartService $service The service responsible for handling cart operations. This service will be injected into the controller to manage cart-related functionality.
     */
    public function __construct(
        protected CartService $service
    )
    {
    }

    /**
     * Display a listing of the resource.
     * @param Request $request The incoming HTTP request containing the authenticated user and guest token (if applicable).
     * @return JsonResponse A JSON response containing the cart data for the authenticated user or guest. The response will have a status code of 200 (OK).
     */
    public function index(Request $request): JsonResponse
    {
        $owner = new CartIdentifierData(
            $request->user(),
            $request->header(config('cart.cart_guest_header'))
        );

        $cart = $this->service->getCart($owner);

        return CartResource::make($cart)
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
