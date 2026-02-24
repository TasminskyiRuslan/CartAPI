<?php

namespace App\Http\Controllers\Api\Cart;

use App\Actions\Cart\DeleteCartAction;
use App\Actions\Cart\FindCartAction;
use App\Data\Cart\CartIdentifierData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Cart\CartResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use OpenApi\Attributes as OA;

class CartController extends Controller
{
    #[OA\Get(
        path: '/cart',
        description: 'Get the current cart for the user.',
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
     * Get the current cart.
     *
     * @param Request $request
     * @param FindCartAction $findCartAction
     * @return JsonResponse
     */
    public function index(Request $request, FindCartAction $findCartAction): JsonResponse
    {
        $cart = $findCartAction->handle(CartIdentifierData::fromRequest($request));

        return CartResource::make($cart?->loadMissing('items.product'))
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_OK);
    }

    #[OA\Delete(
        path: '/cart',
        description: 'Delete the current cart for the user.',
        summary: 'Delete current cart',
        security: [['sanctum' => []], ['guest_token' => []], []],
        tags: ['Cart'],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_NO_CONTENT,
                description: 'The cart deleted.'
            )
        ]
    )]
    /**
     * Delete the current cart.
     *
     * @param Request $request
     * @param DeleteCartAction $deleteCartAction
     * @return Response
     */
    public function destroy(Request $request, DeleteCartAction $deleteCartAction): Response
    {
        $deleteCartAction->handle(CartIdentifierData::fromRequest($request));
        return response()->noContent();
    }
}
