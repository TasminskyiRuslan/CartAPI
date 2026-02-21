<?php

namespace App\Http\Controllers\Api\Cart;

use App\Data\Cart\CartIdentifierData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Cart\CartResource;
use App\Services\Cart\CartService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use OpenApi\Attributes as OA;

class CartController extends Controller
{
    /**
     * Create a new instance of CartController.
     *
     * @param CartService $service The service responsible for handling cart operations.
     */
    public function __construct(
        protected CartService $service
    )
    {
    }

    #[OA\Get(
        path: '/cart',
        description: 'Get the current cart for the user..',
        summary: 'Get current cart',
        security: [['sanctum' => []], ['guest_token' => []], []],
        tags: ['Cart'],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_OK,
                description: 'The cart data returned.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/Cart'
                        )
                    ]
                )
            ),
        ]
    )]
    /**
     * Show the cart identified by the provided information in the request.
     *
     * @param Request $request The incoming HTTP request containing the necessary information to identify the cart.
     * @return JsonResponse A JSON response containing the cart data, formatted using the CartResource, with an HTTP status code of 200 (OK).
     */
    public function index(Request $request): JsonResponse
    {
        $cart = $this->service->find(CartIdentifierData::fromRequest($request));

        return CartResource::make($cart?->loadMissing('items.product'))
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
