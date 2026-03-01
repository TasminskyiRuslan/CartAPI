<?php

namespace App\Http\Controllers\Api\Cart;

use App\Actions\Cart\ClearCartAction;
use App\Actions\Cart\FindCartAction;
use App\Data\Cart\Context\CartIdentifierData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Cart\CartResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class CartController extends Controller
{
    #[OA\Get(
        path: '/cart',
        description: 'Returns the active cart for the authenticated user or guest.',
        summary: 'Get current cart',
        security: [['sanctum' => []], ['guest_token' => []], []],
        tags: ['Cart'],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_OK,
                description: 'Cart retrieved successfully.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/CartResponse'
                        )
                    ]
                )
            ),
        ]
    )]
    /**
     * Clear all items from the current active cart.
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
        description: 'Clears all items from the active cart for the authenticated user or guest.',
        summary: 'Clear current cart',
        security: [['sanctum' => []], ['guest_token' => []]],
        tags: ['Cart'],
        responses: [
            new OA\Response(
                response: SymfonyResponse::HTTP_NO_CONTENT,
                description: 'All items in the cart have been removed.'
            )
        ]
    )]
    /**
     * Delete the current active cart.
     *
     * @param Request $request
     * @param ClearCartAction $clearCartAction
     * @return Response
     */
    public function destroy(Request $request, ClearCartAction $clearCartAction): Response
    {
        $clearCartAction->handle(CartIdentifierData::fromRequest($request));
        return response()->noContent();
    }
}
